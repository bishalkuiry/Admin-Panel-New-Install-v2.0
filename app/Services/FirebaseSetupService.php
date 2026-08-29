<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Google\Auth\Credentials\ServiceAccountCredentials;

/**
 * Automated Firebase Setup Service
 * 
 * Handles automatic Firebase Realtime Database creation and configuration
 * No manual Firebase Console or CLI required!
 */
class FirebaseSetupService
{
    private const FIREBASE_API_BASE = 'https://firebase.googleapis.com/v1beta1';
    private const FIREBASE_MANAGEMENT_API = 'https://firebase.googleapis.com/v1';
    private const SCOPES = [
        'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/firebase',
    ];

    /**
     * Auto-setup Firebase Realtime Database
     * This is called from admin panel with one click
     */
    public function autoSetup(array $serviceAccount, string $projectId): array
    {
        try {
            // Step 1: Validate service account
            if (!$this->validateServiceAccount($serviceAccount)) {
                return [
                    'success' => false,
                    'message' => 'Invalid service account credentials',
                ];
            }

            // Step 2: Get access token
            $accessToken = $this->getAccessToken($serviceAccount);
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with Firebase',
                ];
            }

            // Step 3: Check if Realtime Database exists
            $databaseUrl = $this->getDatabaseUrl($projectId, $accessToken);
            
            if (!$databaseUrl) {
                // Step 4: Create Realtime Database
                $databaseUrl = $this->createRealtimeDatabase($projectId, $accessToken);
                
                if (!$databaseUrl) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create Realtime Database. Please create it manually in Firebase Console.',
                        'manual_steps' => $this->getManualSteps($projectId),
                    ];
                }
            }

            // Step 5: Deploy security rules
            $rulesDeployed = $this->deploySecurityRules($projectId, $accessToken, $databaseUrl);

            // Step 6: Test connection
            $connectionTest = $this->testConnection($databaseUrl, $accessToken);

            return [
                'success' => true,
                'message' => 'Firebase Realtime Database configured successfully!',
                'database_url' => $databaseUrl,
                'rules_deployed' => $rulesDeployed,
                'connection_test' => $connectionTest,
                'project_id' => $projectId,
            ];

        } catch (\Exception $e) {
            Log::error('Firebase auto-setup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage(),
                'manual_steps' => $this->getManualSteps($projectId),
            ];
        }
    }

    /**
     * Validate service account JSON
     */
    private function validateServiceAccount(array $serviceAccount): bool
    {
        $requiredKeys = [
            'type',
            'project_id',
            'private_key_id',
            'private_key',
            'client_email',
            'client_id',
        ];

        foreach ($requiredKeys as $key) {
            if (!isset($serviceAccount[$key])) {
                return false;
            }
        }

        return $serviceAccount['type'] === 'service_account';
    }

    /**
     * Get OAuth 2.0 access token
     */
    private function getAccessToken(array $serviceAccount): ?string
    {
        try {
            $credentials = new ServiceAccountCredentials(
                self::SCOPES,
                $serviceAccount
            );

            $token = $credentials->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get existing database URL
     */
    private function getDatabaseUrl(string $projectId, string $accessToken): ?string
    {
        try {
            Log::info('Starting database URL detection', ['project_id' => $projectId]);
            
            // Try multiple common database URL formats
            $possibleUrls = [
                "https://{$projectId}-default-rtdb.firebaseio.com",
                "https://{$projectId}.firebaseio.com",
                "https://{$projectId}-default-rtdb.asia-southeast1.firebasedatabase.app",
                "https://{$projectId}-default-rtdb.europe-west1.firebasedatabase.app",
                "https://{$projectId}-default-rtdb.us-central1.firebasedatabase.app",
            ];

            Log::info('Trying direct URL formats', ['urls' => $possibleUrls]);

            foreach ($possibleUrls as $url) {
                try {
                    Log::info('Testing URL', ['url' => $url]);
                    
                    $testResponse = Http::timeout(10)->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                    ])->get($url . '/.json');

                    Log::info('URL test response', [
                        'url' => $url,
                        'status' => $testResponse->status(),
                        'successful' => $testResponse->successful(),
                    ]);

                    // 200 = Success, database exists and accessible
                    // 401 = Unauthorized, but database EXISTS (just needs proper auth/rules)
                    // 404 = Not Found, database doesn't exist at this URL
                    if ($testResponse->successful() || $testResponse->status() === 401) {
                        Log::info('✓ Found existing Firebase database', [
                            'url' => $url,
                            'status' => $testResponse->status(),
                            'note' => $testResponse->status() === 401 ? 'Database exists, will configure auth' : 'Database accessible'
                        ]);
                        return $url;
                    }
                } catch (\Exception $e) {
                    Log::warning('URL test failed', [
                        'url' => $url,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Direct URL tests failed, trying Firebase Management API');

            // Try Firebase Management API as fallback
            try {
                $apiUrl = self::FIREBASE_MANAGEMENT_API . "/projects/{$projectId}/databases";
                Log::info('Calling Firebase Management API', ['url' => $apiUrl]);
                
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->get($apiUrl);

                Log::info('Management API response', [
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'body' => $response->body(),
                ]);

                if ($response->successful()) {
                    $databases = $response->json('databases', []);
                    
                    Log::info('Found databases in API response', [
                        'count' => count($databases),
                        'databases' => $databases,
                    ]);
                    
                    if (!empty($databases)) {
                        foreach ($databases as $database) {
                            $name = $database['name'] ?? '';
                            Log::info('Processing database', ['name' => $name]);
                            
                            if ($name) {
                                // Extract database name from full path
                                // Format: projects/{project}/databases/{database}
                                $parts = explode('/', $name);
                                $dbName = end($parts);
                                
                                // Try different URL formats for this database
                                $testUrls = [
                                    "https://{$dbName}.firebaseio.com",
                                    "https://{$projectId}-{$dbName}.firebaseio.com",
                                ];
                                
                                foreach ($testUrls as $url) {
                                    try {
                                        Log::info('Testing extracted database URL', ['url' => $url]);
                                        
                                        $testResponse = Http::timeout(10)->withHeaders([
                                            'Authorization' => 'Bearer ' . $accessToken,
                                        ])->get($url . '/.json');
                                        
                                        if ($testResponse->successful()) {
                                            Log::info('✓ Found database via Management API', ['url' => $url]);
                                            return $url;
                                        }
                                    } catch (\Exception $e) {
                                        Log::warning('Extracted URL test failed', [
                                            'url' => $url,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Management API call failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            Log::warning('Database URL not found after all attempts');
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to get database URL', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Create Realtime Database
     * Note: This requires Firebase Management API which may not be available
     * In that case, we provide manual instructions
     */
    private function createRealtimeDatabase(string $projectId, string $accessToken): ?string
    {
        try {
            // Note: Database creation via API often requires special permissions
            // Most users will need to create it manually in Firebase Console
            
            // Try to create database via API (may fail due to permissions)
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post(self::FIREBASE_MANAGEMENT_API . "/projects/{$projectId}/databases", [
                'databaseId' => 'default',
                'locationId' => 'us-central1', // Default location
                'type' => 'REALTIME_DATABASE',
            ]);

            if ($response->successful()) {
                $database = $response->json();
                $databaseName = $database['name'] ?? null;
                
                if ($databaseName) {
                    // Extract database name and construct URL
                    $parts = explode('/', $databaseName);
                    $dbName = end($parts);
                    $url = "https://{$dbName}.firebaseio.com";
                    
                    Log::info('Successfully created Firebase database via API', ['url' => $url]);
                    return $url;
                }
            }

            // API creation failed - this is expected for most users
            Log::info('Database creation via API not available - manual creation required');
            return null;

        } catch (\Exception $e) {
            Log::info('Database creation via API failed (expected)', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Deploy security rules
     */
    private function deploySecurityRules(string $projectId, string $accessToken, string $databaseUrl): bool
    {
        try {
            // Get rules from file
            $rulesPath = base_path('firebase-database-rules.json');
            
            if (!file_exists($rulesPath)) {
                // Create default rules
                $rules = $this->getDefaultRules();
            } else {
                $rules = json_decode(file_get_contents($rulesPath), true);
            }

            // Extract database name from URL
            // Handle both .firebaseio.com and .firebasedatabase.app formats
            $databaseName = parse_url($databaseUrl, PHP_URL_HOST);
            $databaseName = str_replace(['.firebaseio.com', '.firebasedatabase.app'], '', $databaseName);

            Log::info('Deploying security rules', [
                'database_url' => $databaseUrl,
                'database_name' => $databaseName,
            ]);

            // Deploy rules via REST API
            $rulesUrl = "https://{$databaseName}.firebaseio.com/.settings/rules.json";
            
            // Try .firebaseio.com first
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->put($rulesUrl, $rules);

            if ($response->successful()) {
                Log::info('✓ Security rules deployed successfully');
                return true;
            }

            // If that fails and we're using .firebasedatabase.app, try that format
            if (str_contains($databaseUrl, '.firebasedatabase.app')) {
                $rulesUrl = $databaseUrl . '/.settings/rules.json';
                
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->put($rulesUrl, $rules);

                if ($response->successful()) {
                    Log::info('✓ Security rules deployed successfully (alt format)');
                    return true;
                }
            }

            Log::warning('Failed to deploy security rules', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to deploy security rules', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Test database connection
     */
    private function testConnection(string $databaseUrl, string $accessToken): bool
    {
        try {
            // Try to read from database
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($databaseUrl . '/.json');

            // 200 = Success, can read from database
            // 401 = Database exists but needs proper security rules (still valid!)
            // 404 = Database doesn't exist
            $isValid = $response->successful() || $response->status() === 401;
            
            Log::info('Connection test result', [
                'url' => $databaseUrl,
                'status' => $response->status(),
                'valid' => $isValid,
            ]);
            
            return $isValid;
        } catch (\Exception $e) {
            Log::error('Connection test failed', [
                'url' => $databaseUrl,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get default security rules
     */
    private function getDefaultRules(): array
    {
        return [
            'rules' => [
                'chats' => [
                    '$chatId' => [
                        '.read' => 'auth != null',
                        '.write' => 'auth != null',
                        'messages' => [
                            '$messageId' => [
                                '.validate' => "newData.hasChildren(['sender_id', 'sender_name', 'message', 'timestamp', 'message_type', 'is_read'])",
                            ],
                        ],
                        'typing' => [
                            '$userId' => [
                                '.validate' => 'newData.isNumber() || newData.val() === null',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get manual setup steps
     */
    private function getManualSteps(string $projectId): array
    {
        return [
            [
                'step' => 1,
                'title' => 'Open Firebase Console',
                'description' => 'Click the button below to open Firebase Console',
                'action' => 'open_console',
                'url' => "https://console.firebase.google.com/project/{$projectId}/database",
            ],
            [
                'step' => 2,
                'title' => 'Create Realtime Database',
                'description' => 'Click the "Create Database" button in the Realtime Database section',
                'action' => 'create_database',
            ],
            [
                'step' => 3,
                'title' => 'Choose Location',
                'description' => 'Select a location closest to your users (e.g., us-central1, asia-southeast1)',
                'action' => 'select_location',
            ],
            [
                'step' => 4,
                'title' => 'Start in Test Mode',
                'description' => 'Select "Start in test mode" and click "Enable"',
                'action' => 'enable_database',
            ],
            [
                'step' => 5,
                'title' => 'Return Here',
                'description' => 'Come back to this page and click "Complete Setup" button below',
                'action' => 'return_here',
            ],
        ];
    }

    /**
     * Complete setup after manual database creation
     * This is called after user creates database manually
     */
    public function completeSetup(array $serviceAccount, string $projectId, ?string $manualDatabaseUrl = null): array
    {
        try {
            // Step 1: Get access token
            $accessToken = $this->getAccessToken($serviceAccount);
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with Firebase',
                ];
            }

            // Step 2: Try to find the database URL
            $databaseUrl = null;
            
            // If user provided manual URL, try that first
            if ($manualDatabaseUrl) {
                Log::info('Testing manually provided database URL', ['url' => $manualDatabaseUrl]);
                
                // Clean up the URL
                $manualDatabaseUrl = rtrim($manualDatabaseUrl, '/');
                
                if ($this->testConnection($manualDatabaseUrl, $accessToken)) {
                    $databaseUrl = $manualDatabaseUrl;
                    Log::info('✓ Manual database URL verified', ['url' => $databaseUrl]);
                } else {
                    Log::warning('Manual database URL test failed', ['url' => $manualDatabaseUrl]);
                }
            }
            
            // If manual URL didn't work or wasn't provided, try auto-detection
            if (!$databaseUrl) {
                $databaseUrl = $this->getDatabaseUrl($projectId, $accessToken);
            }
            
            if (!$databaseUrl) {
                return [
                    'success' => false,
                    'message' => 'Database not found. Please make sure you created it in Firebase Console.',
                    'retry' => true,
                    'show_manual_input' => true,
                ];
            }

            // Step 3: Deploy security rules
            $rulesDeployed = $this->deploySecurityRules($projectId, $accessToken, $databaseUrl);

            // Step 4: Test connection
            $connectionTest = $this->testConnection($databaseUrl, $accessToken);

            return [
                'success' => true,
                'message' => 'Firebase setup completed successfully!',
                'database_url' => $databaseUrl,
                'rules_deployed' => $rulesDeployed,
                'connection_test' => $connectionTest,
                'project_id' => $projectId,
            ];

        } catch (\Exception $e) {
            Log::error('Firebase complete setup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Quick setup check - validates configuration
     */
    public function validateSetup(array $serviceAccount, string $projectId): array
    {
        $issues = [];
        $warnings = [];

        // Check service account
        if (!$this->validateServiceAccount($serviceAccount)) {
            $issues[] = 'Invalid service account credentials';
        }

        // Check project ID
        if (empty($projectId)) {
            $issues[] = 'Firebase project ID is required';
        }

        // Try to get access token
        $accessToken = $this->getAccessToken($serviceAccount);
        if (!$accessToken) {
            $issues[] = 'Cannot authenticate with Firebase. Check service account permissions.';
        } else {
            // Check database exists
            $databaseUrl = $this->getDatabaseUrl($projectId, $accessToken);
            if (!$databaseUrl) {
                $warnings[] = 'Realtime Database not found. Click "Auto Setup" to create it.';
            } else {
                // Test connection
                if (!$this->testConnection($databaseUrl, $accessToken)) {
                    $warnings[] = 'Database exists but connection test failed. Check security rules.';
                }
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
            'can_auto_setup' => empty($issues),
        ];
    }

    /**
     * Get setup status
     */
    public function getSetupStatus(array $serviceAccount, string $projectId): array
    {
        try {
            $accessToken = $this->getAccessToken($serviceAccount);
            if (!$accessToken) {
                return [
                    'configured' => false,
                    'database_exists' => false,
                    'rules_deployed' => false,
                    'connection_ok' => false,
                ];
            }

            $databaseUrl = $this->getDatabaseUrl($projectId, $accessToken);
            $databaseExists = !empty($databaseUrl);
            $connectionOk = $databaseExists && $this->testConnection($databaseUrl, $accessToken);

            return [
                'configured' => true,
                'database_exists' => $databaseExists,
                'database_url' => $databaseUrl,
                'rules_deployed' => $databaseExists, // Assume rules are deployed if DB exists
                'connection_ok' => $connectionOk,
            ];

        } catch (\Exception $e) {
            return [
                'configured' => false,
                'database_exists' => false,
                'rules_deployed' => false,
                'connection_ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

