<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

/**
 * Enterprise Hybrid Broadcasting Service
 * 
 * Supports multiple drivers with automatic fallback:
 * 1. Pusher (WebSocket) - Best for real-time
 * 2. Redis (Pub/Sub) - Great for scaling
 * 3. Database/SSE - Works on shared hosting
 * 
 * Admin can configure from settings panel
 */
class BroadcastService
{
    private const CACHE_PREFIX = 'broadcast_events_';
    private const EVENT_TTL = 300;
    private const MAX_EVENTS = 100;

    private ?string $driver = null;
    private array $config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load broadcast configuration from database/cache
     */
    private function loadConfig(): void
    {
        $this->config = Cache::remember('broadcast_config', 3600, function () {
            return $this->getConfigFromDatabase();
        });

        $this->driver = $this->determineDriver();
    }

    /**
     * Get config from database or env
     */
    private function getConfigFromDatabase(): array
    {
        // Try to get from database settings
        try {
            if (class_exists(\App\Models\Setting::class)) {
                $settings = \App\Models\Setting::where('group', 'broadcast')->pluck('value', 'key')->toArray();
                if (!empty($settings)) {
                    return $settings;
                }
            }
        } catch (\Exception $e) {
            // Database not ready yet
        }

        // Fallback to env
        return [
            'driver' => env('BROADCAST_DRIVER', 'database'),
            'pusher_app_id' => env('PUSHER_APP_ID'),
            'pusher_app_key' => env('PUSHER_APP_KEY'),
            'pusher_app_secret' => env('PUSHER_APP_SECRET'),
            'pusher_app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'redis_host' => env('REDIS_HOST', '127.0.0.1'),
            'redis_port' => env('REDIS_PORT', 6379),
            'redis_password' => env('REDIS_PASSWORD'),
        ];
    }

    /**
     * Determine best available driver
     */
    private function determineDriver(): string
    {
        $preferred = $this->config['driver'] ?? 'auto';

        if ($preferred !== 'auto') {
            return $preferred;
        }

        // Auto-detect best driver
        if ($this->isPusherConfigured()) {
            return 'pusher';
        }

        if ($this->isRedisAvailable()) {
            return 'redis';
        }

        return 'database';
    }

    /**
     * Check if Pusher is configured
     */
    private function isPusherConfigured(): bool
    {
        return !empty($this->config['pusher_app_id']) 
            && !empty($this->config['pusher_app_key']) 
            && !empty($this->config['pusher_app_secret']);
    }

    /**
     * Check if Redis is available
     */
    private function isRedisAvailable(): bool
    {
        // Check if Redis extension or Predis is available
        if (!extension_loaded('redis') && !class_exists(\Predis\Client::class)) {
            return false;
        }

        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        } catch (\Error $e) {
            return false;
        }
    }

    /**
     * Get current driver info
     */
    public function getDriverInfo(): array
    {
        return [
            'current_driver' => $this->driver,
            'pusher_configured' => $this->isPusherConfigured(),
            'redis_available' => $this->isRedisAvailable(),
            'config' => [
                'driver' => $this->config['driver'] ?? 'auto',
                'pusher_cluster' => $this->config['pusher_app_cluster'] ?? null,
            ],
        ];
    }

    /**
     * Broadcast event to all listeners
     */
    public function broadcast(string $channel, string $event, array $data): bool
    {
        $payload = [
            'id' => uniqid('evt_', true),
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'driver' => $this->driver,
        ];

        Log::info("Broadcasting event", ['channel' => $channel, 'event' => $event, 'driver' => $this->driver]);

        return match ($this->driver) {
            'pusher' => $this->broadcastViaPusher($channel, $event, $payload),
            'redis' => $this->broadcastViaRedis($channel, $event, $payload),
            default => $this->broadcastViaDatabase($channel, $event, $payload),
        };
    }

    /**
     * Broadcast via Pusher (WebSocket)
     */
    private function broadcastViaPusher(string $channel, string $event, array $payload): bool
    {
        try {
            $pusher = new \Pusher\Pusher(
                $this->config['pusher_app_key'],
                $this->config['pusher_app_secret'],
                $this->config['pusher_app_id'],
                [
                    'cluster' => $this->config['pusher_app_cluster'] ?? 'mt1',
                    'useTLS' => true,
                ]
            );

            $pusher->trigger($channel, $event, $payload);
            
            // Also store in database for SSE fallback
            $this->storeEvent($channel, $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Pusher broadcast failed", ['error' => $e->getMessage()]);
            // Fallback to database
            return $this->broadcastViaDatabase($channel, $event, $payload);
        }
    }

    /**
     * Broadcast via Redis (Pub/Sub)
     */
    private function broadcastViaRedis(string $channel, string $event, array $payload): bool
    {
        try {
            Redis::publish("inallcart.{$channel}", json_encode($payload));
            
            // Also store in database for SSE fallback
            $this->storeEvent($channel, $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Redis broadcast failed", ['error' => $e->getMessage()]);
            // Fallback to database
            return $this->broadcastViaDatabase($channel, $event, $payload);
        }
    }

    /**
     * Broadcast via Database (SSE fallback)
     */
    private function broadcastViaDatabase(string $channel, string $event, array $payload): bool
    {
        return $this->storeEvent($channel, $payload);
    }

    /**
     * Store event in cache/database for SSE
     */
    private function storeEvent(string $channel, array $payload): bool
    {
        try {
            // Store in channel-specific cache
            $events = Cache::get(self::CACHE_PREFIX . $channel, []);
            array_unshift($events, $payload);
            $events = array_slice($events, 0, self::MAX_EVENTS);
            Cache::put(self::CACHE_PREFIX . $channel, $events, self::EVENT_TTL);

            // Store in global feed
            $globalEvents = Cache::get(self::CACHE_PREFIX . 'global', []);
            array_unshift($globalEvents, $payload);
            $globalEvents = array_slice($globalEvents, 0, self::MAX_EVENTS);
            Cache::put(self::CACHE_PREFIX . 'global', $globalEvents, self::EVENT_TTL);

            return true;
        } catch (\Exception $e) {
            Log::error("Event storage failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get events for SSE streaming
     */
    public function getEvents(string $channel, ?string $since = null): array
    {
        $events = Cache::get(self::CACHE_PREFIX . $channel, []);

        if ($since) {
            $events = array_filter($events, fn($e) => $e['timestamp'] > $since);
        }

        return array_values($events);
    }

    /**
     * Stream events via SSE
     */
    public function streamSSE(array $channels = ['global'], ?string $lastEventId = null): void
    {
        // Ignore user abort to allow clean shutdown
        ignore_user_abort(false);
        
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (ob_get_level()) ob_end_clean();

        $lastTimestamp = $lastEventId ? base64_decode($lastEventId) : now()->subSeconds(30)->toISOString();
        
        // Tell client to reconnect after 5 seconds if connection drops
        echo "retry: 5000\n\n";

        $startTime = time();
        $maxDuration = 15; // Reduced from 30 to 15 seconds for faster navigation

        while ((time() - $startTime) < $maxDuration) {
            // Check if client disconnected
            if (connection_aborted()) {
                break;
            }
            
            foreach ($channels as $channel) {
                $events = $this->getEvents($channel, $lastTimestamp);
                foreach ($events as $event) {
                    $eventId = base64_encode($event['timestamp']);
                    echo "id: {$eventId}\n";
                    echo "event: {$event['event']}\n";
                    echo "data: " . json_encode($event) . "\n\n";
                    $lastTimestamp = $event['timestamp'];
                }
            }

            echo ": heartbeat\n\n";
            
            if (!@flush()) {
                break; // Client disconnected
            }

            if (connection_aborted()) break;
            usleep(1000000); // 1 second instead of 0.5 seconds
        }
    }

    /**
     * Get Pusher auth for private channels
     */
    public function pusherAuth(string $channelName, string $socketId): array
    {
        if (!$this->isPusherConfigured()) {
            throw new \Exception('Pusher not configured');
        }

        $pusher = new \Pusher\Pusher(
            $this->config['pusher_app_key'],
            $this->config['pusher_app_secret'],
            $this->config['pusher_app_id'],
            ['cluster' => $this->config['pusher_app_cluster'] ?? 'mt1']
        );

        return json_decode($pusher->authorizeChannel($channelName, $socketId), true);
    }

    /**
     * Clear config cache (call after settings update)
     */
    public function clearConfigCache(): void
    {
        Cache::forget('broadcast_config');
        $this->loadConfig();
    }

    /**
     * Test connection for a driver
     */
    public function testConnection(string $driver): array
    {
        return match ($driver) {
            'pusher' => $this->testPusherConnection(),
            'redis' => $this->testRedisConnection(),
            default => ['success' => true, 'message' => 'Database driver always works'],
        };
    }

    private function testPusherConnection(): array
    {
        if (!$this->isPusherConfigured()) {
            return ['success' => false, 'message' => 'Pusher credentials not configured'];
        }

        try {
            $pusher = new \Pusher\Pusher(
                $this->config['pusher_app_key'],
                $this->config['pusher_app_secret'],
                $this->config['pusher_app_id'],
                ['cluster' => $this->config['pusher_app_cluster'] ?? 'mt1', 'useTLS' => true]
            );
            $pusher->trigger('test-channel', 'test-event', ['test' => true]);
            return ['success' => true, 'message' => 'Pusher connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function testRedisConnection(): array
    {
        if (!extension_loaded('redis') && !class_exists(\Predis\Client::class)) {
            return ['success' => false, 'message' => 'Redis PHP extension not installed'];
        }

        try {
            $pong = Redis::ping();
            return ['success' => true, 'message' => 'Redis connection successful: ' . $pong];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Error $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get available channels
     */
    public function getChannels(): array
    {
        return [
            'orders' => 'Order notifications',
            'inventory' => 'Stock alerts',
            'admin' => 'Admin notifications',
            'user.{id}' => 'User-specific notifications',
        ];
    }
}
