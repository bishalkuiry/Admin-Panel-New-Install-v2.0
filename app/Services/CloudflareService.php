<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';

    /**
     * Get Zone ID for a domain (tries root domain if subdomain fails)
     */
    public function getZoneId(string $email, string $apiKey, string $domain): ?string
    {
        // Remove scheme if present
        $domain = parse_url($domain, PHP_URL_HOST) ?? $domain;
        
        // Remove port if present
        $domain = explode(':', $domain)[0];

        // Break domain into parts to find root zone (e.g. api.store.example.com -> example.com)
        $parts = explode('.', $domain);
        
        while (count($parts) >= 2) {
            $currentDomain = implode('.', $parts);
            
            try {
                $response = Http::withHeaders([
                    'X-Auth-Email' => $email,
                    'X-Auth-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->get("{$this->baseUrl}/zones", [
                    'name' => $currentDomain,
                    'status' => 'active',
                ]);

                if ($response->successful()) {
                    $result = $response->json('result');
                    if (!empty($result)) {
                        return $result[0]['id'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Cloudflare Zone ID lookup failed for {$currentDomain}: " . $e->getMessage());
            }
            
            array_shift($parts); // Try next level up
        }

        return null;
    }

    /**
     * Using specific patterns is safer than global API patterns.
     */
    /**
     * Using specific patterns is safer than global API patterns.
     */
    public function setupOptimization(string $email, string $apiKey, string $zoneId, string $domain = null): array
    {
        // If domain is provided, use it for specific patterns. Otherwise fall back to generic.
        // Generic '*/*api...' is often rejected by Cloudflare validation.
        $prefix = $domain ? "*{$domain}" : "*";

        // We use a single broad rule for API v1 to avoid hitting the 3-rule limit on free plans
        // This covers products, categories, config, everything.
        $patterns = [
            "{$prefix}/api/v1/*"
        ];

        $successCount = 0;
        $errors = [];

        foreach ($patterns as $pattern) {
            $result = $this->createOrUpdatePageRule($email, $apiKey, $zoneId, $pattern);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "{$pattern}: " . ($result['error'] ?? 'Unknown error');
            }
        }

        $message = "Configured {$successCount} optimization rules.";
        if (count($errors) > 0) {
            $message .= " Failed: " . implode(', ', $errors);
        }

        return [
            'success' => $successCount > 0,
            'message' => $message,
            'details' => [
                'rules_configured' => $successCount,
                'errors' => $errors
            ]
        ];
    }

    /**
     * Create or update a single Page Rule
     */
    protected function createOrUpdatePageRule(string $email, string $apiKey, string $zoneId, string $pattern): array
    {
        try {
            // 1. Check if rule exists
            $response = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/zones/{$zoneId}/pagerules");

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Failed to list rules: ' . $response->body()];
            }

            $existingRuleId = null;
            foreach ($response->json('result', []) as $rule) {
                if (isset($rule['targets'][0]['constraint']['value']) && $rule['targets'][0]['constraint']['value'] === $pattern) {
                    $existingRuleId = $rule['id'];
                    break;
                }
            }

            // 2. Prepare payload
            // We ONLY set 'Cache Everything'. 
            // We do NOT set 'Edge Cache TTL' because it requires high-tier plans for low values (< 2h).
            // Instead, Cloudflare will respect our origin's 'Cache-Control: public, max-age=60' header.
            $ruleData = [
                'targets' => [
                    [
                        'target' => 'url',
                        'constraint' => [
                            'operator' => 'matches',
                            'value' => $pattern
                        ]
                    ]
                ],
                'actions' => [
                    [
                        'id' => 'cache_level',
                        'value' => 'cache_everything'
                    ]
                ],
                'priority' => 1,
                'status' => 'active'
            ];

            if ($existingRuleId) {
                $res = Http::withHeaders([
                    'X-Auth-Email' => $email,
                    'X-Auth-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->put("{$this->baseUrl}/zones/{$zoneId}/pagerules/{$existingRuleId}", $ruleData);
            } else {
                $res = Http::withHeaders([
                    'X-Auth-Email' => $email,
                    'X-Auth-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->post("{$this->baseUrl}/zones/{$zoneId}/pagerules", $ruleData);
            }

            if (!$res->successful()) {
                $errorBody = $res->json();
                $errorMsg = $errorBody['errors'][0]['message'] ?? $res->body();
                Log::error("Cloudflare Page Rule failed for {$pattern}: {$errorMsg}");
                return ['success' => false, 'error' => $errorMsg];
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("Cloudflare Page Rule setup failed for pattern {$pattern}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Purge specific URLs from cache
     */
    public function purgeUrls(string $email, string $apiKey, string $zoneId, array $urls): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/zones/{$zoneId}/purge_cache", [
                'files' => $urls
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Cloudflare Purge URLs failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Purge all cache for the zone
     */
    public function purgeCache(string $email, string $apiKey, string $zoneId): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/zones/{$zoneId}/purge_cache", [
                'purge_everything' => true
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Cloudflare Purge failed: " . $e->getMessage());
            return false;
        }
    }
}
