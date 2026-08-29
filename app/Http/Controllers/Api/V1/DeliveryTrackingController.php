<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppContent;
use App\Models\DeliveryTracking;
use App\Models\Order;
use App\Models\Setting;
use App\Services\RealtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryTrackingController extends Controller
{
    public function __construct(
        private RealtimeService $realtimeService
    ) {}

    /**
     * Get tracking data with ETag support (SAME PATTERN AS APP CONTENT)
     * 
     * This endpoint uses the EXACT SAME caching strategy as your app content widgets:
     * - Cache-first approach
     * - ETag validation
     * - 304 responses when data unchanged
     * - Instant updates via RealtimeService
     */
    public function getTrackingData(Request $request, Order $order)
    {
        // Verify user owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
        
        // Get tracking data from Redis cache (SAME AS APP CONTENT)
        $trackingData = Cache::remember(
            "tracking:order:{$order->id}",
            60, // 1 minute TTL (same as products)
            function() use ($order) {
                return $this->buildTrackingData($order);
            }
        );
        
        // Generate ETag from tracking data (SAME AS APP CONTENT)
        $etag = md5(json_encode($trackingData));
        
        // Check if client has latest version (SAME AS APP CONTENT)
        if ($request->header('If-None-Match') === $etag) {
            // No changes - return 304 (super fast, no data transfer)
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'max-age=10'); // Poll every 10 seconds
        }
        
        // Data changed - return full response
        return response()->json($trackingData)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'max-age=10');
    }

    /**
     * Update delivery partner location (called from delivery partner app)
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'accuracy' => 'nullable|numeric|min:0',
        ]);
        
        $order = Order::findOrFail($validated['order_id']);
        
        // Verify delivery partner is assigned to this order
        if ($order->delivery_partner_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
        
        // Update order current location (for quick access)
        $order->update([
            'current_latitude' => $validated['latitude'],
            'current_longitude' => $validated['longitude'],
            'last_location_update' => now(),
        ]);
        
        // Store in tracking history (for route replay)
        DeliveryTracking::create([
            'order_id' => $order->id,
            'delivery_partner_id' => $request->user()->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'],
            'accuracy' => $validated['accuracy'],
            'status' => $this->determineStatus($validated['speed'] ?? 0),
            'recorded_at' => now(),
        ]);
        
        // Clear cache to force new ETag (SAME AS APP CONTENT UPDATES)
        Cache::forget("tracking:order:{$order->id}");
        
        // Broadcast via RealtimeService for instant updates (SAME AS APP CONTENT)
        $this->realtimeService->notifyUser(
            $order->user_id,
            'delivery-location-updated',
            [
                'order_id' => $order->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'speed' => $validated['speed'],
                'eta_minutes' => $this->calculateETA($order),
                'status' => $order->status,
                'last_update' => now()->toIso8601String(),
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
        ]);
    }

    /**
     * Build tracking data structure
     */
    private function buildTrackingData(Order $order): array
    {
        $order->load(['deliveryTracking', 'address', 'store', 'user', 'deliveryPartner', 'items.product.primaryImage']);
        
        // Get delivery partner's current location
        $partnerLat = null;
        $partnerLng = null;
        $lastUpdate = null;
        
        if ($order->deliveryPartner) {
            // First try to get from order's current location (most recent)
            if ($order->current_latitude && $order->current_longitude) {
                $partnerLat = $order->current_latitude;
                $partnerLng = $order->current_longitude;
                $lastUpdate = $order->last_location_update;
            } 
            // Fallback: Get delivery partner's last known location from any order
            else {
                $lastTracking = DeliveryTracking::where('delivery_partner_id', $order->deliveryPartner->id)
                    ->latest('recorded_at')
                    ->first();
                
                if ($lastTracking) {
                    $partnerLat = $lastTracking->latitude;
                    $partnerLng = $lastTracking->longitude;
                    $lastUpdate = $lastTracking->recorded_at;
                }
            }
        }
        
        // Get user's current location from session/profile or use order address
        $userLat = $order->address?->latitude;
        $userLng = $order->address?->longitude;
        
        // If address coordinates are null, try to get from user's current location
        if (!$userLat || !$userLng) {
            // Try to get from user's last known location (stored in session or user profile)
            $userLat = $order->user?->current_latitude ?? null;
            $userLng = $order->user?->current_longitude ?? null;
        }
        
        // Fallback to Lalli Lane, Bangalore (560037) if still null
        if (!$userLat || !$userLng) {
            $userLat = 12.9352; // Lalli Lane, Bangalore
            $userLng = 77.6245;
        }
        
        // Get store coordinates with fallback
        $storeLat = $order->store?->latitude ?? 12.9716; // Bangalore default
        $storeLng = $order->store?->longitude ?? 77.5946;
        
        // Get tracking media widget (if enabled for tracking screen)
        $trackingMedia = AppContent::where('type', 'media')
            ->where('style', 'style_1')
            ->where('show_on_tracking', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        $trackingMediaItems = [];
        foreach ($trackingMedia as $widget) {
            if (!empty($widget->media_items)) {
                foreach ($widget->media_items as $item) {
                    $imageUrl = $item['url'] ?? null;
                    
                    // Format image URL to full path
                    if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                        if (!str_starts_with($imageUrl, '/storage/')) {
                            $imageUrl = '/storage/' . $imageUrl;
                        }
                        $imageUrl = url($imageUrl);
                    }
                    
                    $trackingMediaItems[] = [
                        'id' => $item['link_id'] ?? null,
                        'title' => null,
                        'subtitle' => null,
                        'image' => $imageUrl,
                        'action_type' => $item['link_type'] ?? 'none',
                        'action_value' => $item['link_id'] ?? $item['link_url'] ?? null,
                    ];
                }
            }
        }
        
        // Calculate total savings
        $totalSavings = $order->discount + $order->items->sum(function ($item) {
            return ($item->original_price - $item->price) * $item->quantity;
        });
        
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'current_location' => $partnerLat && $partnerLng ? [
                'latitude' => $partnerLat,
                'longitude' => $partnerLng,
                'updated_at' => $lastUpdate?->toIso8601String(),
            ] : null,
            'delivery_partner' => $order->deliveryPartner ? [
                'id' => $order->deliveryPartner->id,
                'name' => $order->deliveryPartner->name,
                'phone' => $order->deliveryPartner->phone,
                'photo' => storage_url($order->deliveryPartner->avatar),
                'rating' => $order->deliveryPartner->rating ?? 0,
            ] : null,
            'destination' => [
                'latitude' => $userLat,
                'longitude' => $userLng,
                'address' => $order->address?->address_line_1 ?? 'Delivery Address',
                'city' => $order->address?->city ?? 'Bangalore',
            ],
            'store' => $order->store ? [
                'id' => $order->store->id,
                'name' => $order->store->name,
                'latitude' => $storeLat,
                'longitude' => $storeLng,
                'image' => $order->store->logo ? storage_url($order->store->logo) : null,
                'phone' => $order->store->phone,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'original_price' => $item->original_price ?? $item->price,
                    'total' => $item->price * $item->quantity,
                    'image' => $item->product && $item->product->primaryImage 
                        ? storage_url($item->product->primaryImage->image) 
                        : null,
                ];
            }),
            'total_items' => $order->items->count(),
            'total_savings' => $totalSavings,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
            'tracking_media' => $trackingMediaItems,
            'eta_minutes' => $this->calculateETA($order),
            'estimated_delivery_at' => $order->estimated_delivery_at?->toIso8601String(),
            'tracking_available' => $partnerLat !== null && $partnerLng !== null,
        ];
    }

    /**
     * Calculate ETA (Estimated Time of Arrival).
     *
     * Priority order:
     *   1. Google Maps Distance Matrix — real road distance + live traffic.
     *      Called only when the delivery partner's location is known (they are
     *      moving). Result is cached for 30 seconds so rapid customer polling
     *      does not multiply API calls.
     *   2. Zone-based estimation — straight-line distance × zone time factors.
     *      Used before a partner is assigned or when Maps API is unavailable.
     *   3. Flat 30-minute fallback.
     */
    private function calculateETA(Order $order): ?int
    {
        $hasLiveLocation = $order->current_latitude && $order->current_longitude;
        $destination     = $order->address;
        $hasDestination  = $destination && $destination->latitude && $destination->longitude;

        // ── Path 1: partner is moving — use Google Maps Distance Matrix ──────
        if ($hasLiveLocation && $hasDestination) {
            $mapsEta = $this->getGoogleMapsETA(
                (float) $order->current_latitude,
                (float) $order->current_longitude,
                (float) $destination->latitude,
                (float) $destination->longitude,
                $order->id
            );

            if ($mapsEta !== null) {
                // Add packing buffer if order is still being prepared
                if (in_array($order->status?->value ?? $order->status, ['confirmed', 'packed'])) {
                    $mapsEta += (int) ($order->store?->preparation_time ?? 10);
                }
                return $mapsEta;
            }

            // Maps API failed — fall back to Haversine so we still return something
            $distance = $this->calculateDistance(
                $order->current_latitude,
                $order->current_longitude,
                $destination->latitude,
                $destination->longitude
            );
            $avgSpeed  = $this->getAverageSpeed($order->id) ?: 20;
            $etaMinutes = (int) ceil(($distance / $avgSpeed) * 60 * 1.2);

            if (in_array($order->status?->value ?? $order->status, ['confirmed', 'packed'])) {
                $etaMinutes += (int) ($order->store?->preparation_time ?? 10);
            }
            return $etaMinutes;
        }

        // ── Path 2: no live location yet — zone-based or Maps store→customer ─
        if ($hasDestination && $order->store?->latitude && $order->store?->longitude) {
            // Try Google Maps from store to customer (initial ETA before dispatch)
            $mapsEta = $this->getGoogleMapsETA(
                (float) $order->store->latitude,
                (float) $order->store->longitude,
                (float) $destination->latitude,
                (float) $destination->longitude,
                $order->id
            );

            if ($mapsEta !== null) {
                $packingTime = (int) ($order->store->preparation_time ?? 10);
                return $mapsEta + $packingTime;
            }
        }

        // ── Path 3: zone-based fallback ───────────────────────────────────────
        return $this->getZoneBasedETA($order);
    }

    /**
     * Call Google Maps Routes API (Compute Route Matrix) and return driving minutes.
     *
     * Uses the Routes API which replaces the Legacy Distance Matrix API.
     * - Basic SKU (no traffic): 10,000 free elements/month
     * - Advanced SKU (TRAFFIC_AWARE): 5,000 free elements/month
     *
     * We request TRAFFIC_AWARE for live traffic. If the key has no billing
     * account or quota is exceeded, we fall back to TRAFFIC_UNAWARE (Basic SKU,
     * more free calls) and then to Haversine.
     *
     * Cost-saving strategy:
     *   - Result cached 30 seconds per order — absorbs rapid customer polling
     *     (every 10 s) so only one real API call fires per 30-second window.
     *   - 1 origin × 1 destination = 1 element per call.
     *   - Called only when both origin and destination coordinates are known.
     *
     * Returns driving minutes, or null on any error so callers fall back.
     */
    private function getGoogleMapsETA(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
        int   $orderId
    ): ?int {
        // 30-second cache — absorbs rapid customer polling without extra API calls.
        // Cache key includes rounded coordinates so a moving partner invalidates
        // the cache when they've moved more than ~100 m.
        $cacheKey = "eta:maps:{$orderId}:"
                  . round($originLat, 4) . ':'
                  . round($originLng, 4);

        return Cache::remember($cacheKey, 30, function () use (
            $originLat, $originLng, $destLat, $destLng, $orderId
        ) {
            // Key is stored in admin Settings → Mobile App → Google Maps API Key.
            // Admin panel path: Settings → Mobile App tab → Map Provider section.
            $apiKey = Setting::get('google_maps_api_key');

            if (!$apiKey) {
                return null;
            }

            // Routes API endpoint (replaces Legacy Distance Matrix API)
            $url = 'https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix';

            $body = [
                'origins' => [[
                    'waypoint' => [
                        'location' => [
                            'latLng' => [
                                'latitude'  => $originLat,
                                'longitude' => $originLng,
                            ],
                        ],
                    ],
                ]],
                'destinations' => [[
                    'waypoint' => [
                        'location' => [
                            'latLng' => [
                                'latitude'  => $destLat,
                                'longitude' => $destLng,
                            ],
                        ],
                    ],
                ]],
                // TRAFFIC_AWARE uses live traffic (Advanced SKU, 5,000 free/month).
                // Falls back to TRAFFIC_UNAWARE (Basic SKU, 10,000 free/month)
                // if the response fails.
                'routingPreference' => 'TRAFFIC_AWARE',
                'travelMode'        => 'DRIVE',
            ];

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'X-Goog-Api-Key'    => $apiKey,
                        // Field mask — only request the fields we need.
                        // Requesting fewer fields = lower SKU tier = more free calls.
                        'X-Goog-FieldMask'  => 'originIndex,destinationIndex,duration,status',
                        'Content-Type'      => 'application/json',
                    ])
                    ->post($url, $body);

                if ($response->successful()) {
                    $rows = $response->json();

                    // Routes API returns a JSON array of route elements
                    $element = is_array($rows) ? ($rows[0] ?? null) : null;

                    if ($element && ($element['status'] ?? 'OK') === 'OK') {
                        // duration is a string like "720s"
                        $durationStr = $element['duration'] ?? null;
                        if ($durationStr) {
                            $seconds = (int) rtrim($durationStr, 's');
                            return $seconds > 0 ? (int) ceil($seconds / 60) : null;
                        }
                    }
                }

                // TRAFFIC_AWARE failed — retry with TRAFFIC_UNAWARE (Basic SKU)
                $body['routingPreference'] = 'TRAFFIC_UNAWARE';
                $response2 = Http::timeout(5)
                    ->withHeaders([
                        'X-Goog-Api-Key'   => $apiKey,
                        'X-Goog-FieldMask' => 'originIndex,destinationIndex,duration,status',
                        'Content-Type'     => 'application/json',
                    ])
                    ->post($url, $body);

                if ($response2->successful()) {
                    $rows2    = $response2->json();
                    $element2 = is_array($rows2) ? ($rows2[0] ?? null) : null;

                    if ($element2 && ($element2['status'] ?? 'OK') === 'OK') {
                        $durationStr2 = $element2['duration'] ?? null;
                        if ($durationStr2) {
                            $seconds2 = (int) rtrim($durationStr2, 's');
                            return $seconds2 > 0 ? (int) ceil($seconds2 / 60) : null;
                        }
                    }
                }

                return null;

            } catch (\Throwable $e) {
                Log::warning(
                    'DeliveryTrackingController: Google Maps Routes API ETA failed',
                    ['order_id' => $orderId, 'error' => $e->getMessage()]
                );
                return null;
            }
        });
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Get average speed from recent tracking data
     */
    private function getAverageSpeed(int $orderId): ?float
    {
        // Get last 5 minutes of tracking data
        $recentTracking = DeliveryTracking::where('order_id', $orderId)
            ->where('recorded_at', '>=', now()->subMinutes(5))
            ->where('speed', '>', 0)
            ->avg('speed');
        
        return $recentTracking;
    }

    /**
     * Get zone-based ETA estimation.
     *
     * Wrapped in try/catch because the zones table may not exist on all
     * deployments (e.g. servers that haven't run the zones migration yet).
     * A missing table must not crash the tracking endpoint — fall back to
     * the default 30-minute estimate instead.
     */
    private function getZoneBasedETA(Order $order): ?int
    {
        try {
            if (!$order->address || !$order->address->latitude || !$order->address->longitude) {
                return 30;
            }

            $zone = $order->address->findZone();

            if ($zone && $order->store && $order->store->latitude && $order->store->longitude) {
                $distance = $this->calculateDistance(
                    $order->store->latitude,
                    $order->store->longitude,
                    $order->address->latitude,
                    $order->address->longitude
                );

                return $zone->base_delivery_time_minutes +
                       ($distance * $zone->per_km_time_minutes);
            }
        } catch (\Throwable $e) {
            // zones table missing or spatial query unsupported — fall through
            Log::warning(
                'DeliveryTrackingController: getZoneBasedETA failed, using default',
                ['order_id' => $order->id, 'error' => $e->getMessage()]
            );
        }

        // Fallback: 30 minutes
        return 30;
    }

    /**
     * Determine status based on speed
     */
    private function determineStatus(float $speed): string
    {
        if ($speed < 1) {
            return 'stopped';
        }
        
        return 'moving';
    }

    /**
     * Get tracking history (for route replay)
     */
    public function getTrackingHistory(Request $request, Order $order)
    {
        // Verify user owns this order
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
        
        $history = DeliveryTracking::where('order_id', $order->id)
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->map(function ($tracking) {
                return [
                    'latitude' => $tracking->latitude,
                    'longitude' => $tracking->longitude,
                    'speed' => $tracking->speed,
                    'status' => $tracking->status,
                    'recorded_at' => $tracking->recorded_at->toIso8601String(),
                ];
            });
        
        return response()->json([
            'order_id' => $order->id,
            'history' => $history,
        ]);
    }
}
