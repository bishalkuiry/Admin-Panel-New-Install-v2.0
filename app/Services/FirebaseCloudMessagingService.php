<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;

/**
 * Firebase Cloud Messaging Service (HTTP v1 API)
 * Uses OAuth 2.0 with service account credentials
 * 
 * Setup:
 * 1. Download service account JSON from Firebase Console
 * 2. Store path in FIREBASE_CREDENTIALS env variable
 * 3. Or upload JSON content to settings
 */
class FirebaseCloudMessagingService
{
    private const FCM_V1_URL = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';
    private const SCOPES = ['https://www.googleapis.com/auth/firebase.messaging'];

    private ?string $projectId;
    private ?array $serviceAccount;
    private string $scheme;

    public function __construct()
    {
        $this->scheme = strtolower(config('app.name', 'quixko'));
        $settings = Setting::getGroup('mobile_app');
        $this->projectId = $settings['firebase_project_id'] ?? null;

        // Try to load service account from file or settings
        $this->serviceAccount = $this->loadServiceAccount($settings);
    }

    /**
     * Load service account credentials
     */
    private function loadServiceAccount(array $settings): ?array
    {
        // Try from environment variable (file path)
        $credentialsPath = env('FIREBASE_CREDENTIALS');
        if ($credentialsPath && file_exists($credentialsPath)) {
            $json = file_get_contents($credentialsPath);
            return json_decode($json, true);
        }

        // Try from settings (JSON content)
        if (!empty($settings['firebase_service_account'])) {
            return json_decode($settings['firebase_service_account'], true);
        }

        return null;
    }

    /**
     * Check if FCM is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->projectId) && !empty($this->serviceAccount);
    }

    /**
     * Get OAuth 2.0 access token (cached for 1 hour)
     */
    private function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        return Cache::remember('fcm_access_token', 3500, function () {
            try {
                $credentials = new ServiceAccountCredentials(
                    self::SCOPES,
                    $this->serviceAccount
                );

                $token = $credentials->fetchAuthToken();
                return $token['access_token'] ?? null;
            } catch (\Exception $e) {
                Log::error('Failed to get FCM access token', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Send notification to a single device (v1 API)
     */
    public function sendToDevice(string $fcmToken, array $notification, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('FCM not configured - skipping notification');
            return false;
        }

        // Custom sound: notification['sound'] => e.g. 'new_order' (must be bundled in app raw folder)
        $sound = $notification['sound'] ?? 'new_order';
        // Android channel: notification['channel_id'] => custom channel with matching sound
        $channelId = $notification['channel_id'] ?? 'inallcart_order_alert_sound_v10';

        $message = [
            'token' => $fcmToken,
            'notification' => [
                'title' => $notification['title'] ?? config('app.name', 'InAllCart'),
                'body' => $notification['body'] ?? '',
            ],
            'data' => $this->convertDataToStrings($data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => $sound,
                    'channel_id' => $channelId,
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => $sound === 'default' ? 'default' : $sound . '.mp3',
                        'badge' => 1,
                    ],
                ],
            ],
        ];

        // Add image if provided
        if (!empty($notification['image'])) {
            $message['notification']['image'] = $notification['image'];
        }

        return $this->send(['message' => $message]);
    }

    /**
     * Send notification to multiple devices (batch)
     */
    public function sendToDevices(array $fcmTokens, array $notification, array $data = []): bool
    {
        if (!$this->isConfigured() || empty($fcmTokens)) {
            return false;
        }

        $success = true;

        // v1 API doesn't support batch sending directly
        // Send to each token individually (can be optimized with async)
        foreach ($fcmTokens as $token) {
            $result = $this->sendToDevice($token, $notification, $data);
            if (!$result) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, array $notification, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('FCM not configured - skipping notification');
            return false;
        }

        // Respect caller-provided sound and channel_id (e.g. from RealtimeService)
        $sound = $notification['sound'] ?? 'new_order';
        $channelId = $notification['channel_id'] ?? 'inallcart_order_alert_sound_v10';

        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $notification['title'] ?? config('app.name', 'InAllCart'),
                'body' => $notification['body'] ?? '',
            ],
            'data' => $this->convertDataToStrings($data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => $sound,
                    'channel_id' => $channelId,
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => ($sound === 'default') ? 'default' : $sound . '.mp3',
                        'badge' => 1,
                    ],
                ],
            ],
        ];

        if (!empty($notification['image'])) {
            $message['notification']['image'] = $notification['image'];
        }

        return $this->send(['message' => $message]);
    }

    /**
     * Send data-only message (silent notification)
     */
    public function sendDataMessage(string $fcmToken, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $message = [
            'token' => $fcmToken,
            'data' => $this->convertDataToStrings($data),
            'android' => [
                'priority' => 'high',
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'content-available' => 1,
                    ],
                ],
            ],
        ];

        return $this->send(['message' => $message]);
    }

    /**
     * Convert data values to strings (FCM v1 requirement)
     */
    private function convertDataToStrings(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = is_string($value) ? $value : (string) $value;
        }
        return $result;
    }

    /**
     * Send notification to user
     */
    public function sendToUser(User $user, array $notification, array $data = []): bool
    {
        if (!$user->fcm_token) {
            Log::info('User has no FCM token', ['user_id' => $user->id]);
            return false;
        }

        return $this->sendToDevice($user->fcm_token, $notification, $data);
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $users, array $notification, array $data = []): bool
    {
        $tokens = collect($users)
            ->pluck('fcm_token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::info('No FCM tokens found for users');
            return false;
        }

        return $this->sendToDevices($tokens, $notification, $data);
    }

    /**
     * Send order status update notification
     */
    public function sendOrderStatusUpdate(User $user, array $orderData): bool
    {
        $statusLabels = [
            'pending' => 'Order Placed',
            'confirmed' => 'Order Confirmed',
            'packed' => 'Order Packed',
            'picked_up' => 'Order Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
        ];

        $status = $orderData['new_status'] ?? 'updated';
        $statusLabel = $statusLabels[$status] ?? 'Order Updated';

        return $this->sendToUser($user, [
            'title' => $statusLabel,
            'body' => "Your order #{$orderData['order_number']} has been {$statusLabel}",
        ], [
            'type' => 'order_status',
            'order_id' => (string) $orderData['order_id'],
            'order_number' => $orderData['order_number'],
            'status' => $status,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            // Deep link data
            'deep_link' => $this->scheme . '://order/' . $orderData['order_id'],
            'screen' => 'order_detail',
            'params' => json_encode([
                'orderId' => $orderData['order_id'],
                'orderNumber' => $orderData['order_number'],
            ]),
        ]);
    }

    /**
     * Send new order notification to admin
     */
    public function sendNewOrderNotification(array $orderData): bool
    {
        $formattedTotal = \App\Helpers\CurrencyHelper::format((float)($orderData['total'] ?? 0));
        return $this->sendToTopic('admin', [
            'title' => 'New Order Received',
            'body' => "Order #{$orderData['order_number']} - {$orderData['customer']} - {$formattedTotal}",
        ], [
            'type' => 'new_order',
            'order_id' => (string) $orderData['order_id'],
            'order_number' => $orderData['order_number'],
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ]);
    }

    /**
     * Send promotion notification
     */
    public function sendPromotionNotification(string $title, string $message, ?string $imageUrl = null): bool
    {
        $notification = [
            'title' => $title,
            'body' => $message,
        ];

        if ($imageUrl) {
            $notification['image'] = $imageUrl;
        }

        return $this->sendToTopic('promotions', $notification, [
            'type' => 'promotion',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'deep_link' => $this->scheme . '://home',
            'screen' => 'home',
        ]);
    }

    /**
     * Send product notification
     */
    public function sendProductNotification(string $title, string $message, int $productId, ?string $imageUrl = null): bool
    {
        $notification = [
            'title' => $title,
            'body' => $message,
        ];

        if ($imageUrl) {
            $notification['image'] = $imageUrl;
        }

        return $this->sendToTopic('promotions', $notification, [
            'type' => 'product',
            'product_id' => (string) $productId,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'deep_link' => $this->scheme . "://product/{$productId}",
            'screen' => 'product_detail',
            'params' => json_encode(['productId' => $productId]),
        ]);
    }

    /**
     * Send low stock alert to admin
     */
    public function sendLowStockAlert(array $productData): bool
    {
        return $this->sendToTopic('admin', [
            'title' => 'Low Stock Alert',
            'body' => "{$productData['product_name']} is running low ({$productData['quantity']} left)",
        ], [
            'type' => 'low_stock',
            'product_id' => (string) $productData['product_id'],
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ]);
    }

    /**
     * Send the actual HTTP request to FCM v1 API
     */
    private function send(array $payload): bool
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('No FCM access token available');
            return false;
        }

        $url = str_replace('{project_id}', $this->projectId, self::FCM_V1_URL);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('FCM v1 notification sent successfully', [
                    'message_id' => $response->json('name'),
                ]);
                return true;
            }

            Log::error('FCM v1 notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Clear token cache if unauthorized
            if ($response->status() === 401) {
                Cache::forget('fcm_access_token');
            }

            // Clean up stale/unregistered device tokens
            if ($response->status() === 404) {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                if ($errorCode === 'UNREGISTERED') {
                    $staleToken = $payload['message']['token'] ?? null;
                    if ($staleToken) {
                        \App\Models\User::where('fcm_token', $staleToken)
                            ->update(['fcm_token' => null]);
                        Log::info('Removed stale FCM token (UNREGISTERED)', [
                            'token_prefix' => substr($staleToken, 0, 10) . '...',
                        ]);
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('FCM v1 notification exception', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Subscribe device to topic (v1 API)
     */
    public function subscribeToTopic(string $fcmToken, string $topic): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://iid.googleapis.com/iid/v1/{$fcmToken}/rel/topics/{$topic}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM topic subscription failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe device from topic
     */
    public function unsubscribeFromTopic(string $fcmToken, string $topic): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://iid.googleapis.com/iid/v1:batchRemove", [
                        'to' => "/topics/{$topic}",
                        'registration_tokens' => [$fcmToken],
                    ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM topic unsubscription failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
