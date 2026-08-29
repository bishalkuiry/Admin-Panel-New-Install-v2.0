<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Zone;
use App\Models\Address;
use App\Models\User;
use App\Models\Coupon;
use App\Models\DeliveryCalculationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryCalculationService
{
    public function __construct(
        private ReferralService $referralService
    ) {}
    /**
     * Calculate delivery charge with caching and logging
     * 
     * @param array $context Calculation context
     * @return array Result with charge and breakdown
     */
    public function calculate(array $context): array
    {
        $storeId    = $context['store_id'] ?? null;
        $addressId  = $context['address_id'] ?? null;
        $subtotal   = $context['subtotal'] ?? 0;
        $distance   = $context['distance'] ?? null;
        $userId     = $context['user_id'] ?? null;
        $orderId    = $context['order_id'] ?? null;
        $cartId     = $context['cart_id'] ?? null;
        $couponCode = $context['coupon_code'] ?? null;

        // Only write a DB log row when this calculation is tied to an actual
        // order placement (order_id is set by OrderService after the order is
        // created). Cart-summary reads (GET /cart, quantity taps, page loads)
        // pass order_id=null and must NOT write a log row — the table would
        // grow unboundedly otherwise.
        $shouldLog = $orderId !== null;

        // Try session cache for the strategy first (faster than global cache for the same user)
        $sessionKey = "checkout_delivery_{$userId}_{$storeId}_{$addressId}_{$couponCode}";
        if ($userId && session()->has($sessionKey)) {
            $cached = session()->get($sessionKey);
            if ($cached['subtotal'] == $subtotal && $cached['distance'] == $distance) {
                return $cached['result'];
            }
        }

        // Try global cache
        $cacheKey = $this->getCacheKey($storeId, $addressId, $subtotal, $distance, $couponCode, $userId);

        $result = Cache::remember($cacheKey, 60, function () use ($storeId, $addressId, $subtotal, $distance, $userId, $couponCode) {
            return $this->calculateFresh($storeId, $addressId, $subtotal, $distance, $userId, $couponCode);
        });

        // Save to session for ultra-fast re-access during current checkout
        if ($userId) {
            session()->put($sessionKey, [
                'subtotal' => $subtotal,
                'distance' => $distance,
                'result'   => $result,
            ]);
        }

        // Only log when tied to an order — never on cart-summary reads.
        if ($shouldLog) {
            $wasCached = Cache::has($cacheKey);
            $logId = $this->logCalculation(array_merge($result, [
                'order_id'    => $orderId,
                'cart_id'     => $cartId,
                'user_id'     => $userId,
                'store_id'    => $storeId,
                'address_id'  => $addressId,
                'subtotal'    => $subtotal,
                'distance_km' => $distance,
                'was_cached'  => $wasCached,
            ]));

            if ($logId) {
                $result['log_id'] = $logId;
            }
        }

        return $result;
    }

    /**
     * Calculate delivery charge without cache
     */
    /**
     * Calculate delivery charge without cache
     */
    private function calculateFresh(?int $storeId, ?int $addressId, float $subtotal, ?float $distance, ?int $userId = null, ?string $couponCode = null): array
    {
        // No store ID or Address ID - cannot calculate zone-based
        if (!$storeId || !$addressId) {
             return [
                'is_deliverable' => false,
                'message' => 'Please select a delivery address to check availability.',
                'final_charge' => 0,
                'strategy_used' => 'none',
                'calculation_method' => 'unavailable',
                'base_fee' => 0,
                'distance_fee' => 0,
                'zone_fee' => 0,
                'was_free_delivery' => false,
                'calculation_steps' => [],
            ];
        }

        $store = Store::find($storeId);
        
        if (!$store) {
             return [
                'is_deliverable' => false,
                'message' => 'Store not found.',
                'final_charge' => 0,
                'strategy_used' => 'none',
                'calculation_method' => 'unavailable',
                'base_fee' => 0,
                'distance_fee' => 0,
                'zone_fee' => 0,
                'was_free_delivery' => false,
                'calculation_steps' => [],
            ];
        }

        // Check for Free Delivery Coupon
        $user = $userId ? User::find($userId) : null;
        if ($user && $couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->type === 'free_delivery' && $coupon->canBeUsedBy($user)) {
                // Strict check: User must still be in a valid deliverable zone
                $zoneResult = $this->calculateZoneBased($store, $addressId, $distance);
                if (!$zoneResult) {
                    return [
                        'is_deliverable' => false,
                        'message' => 'Service not available in this area.',
                        'final_charge' => 0,
                        'strategy_used' => 'none',
                        'calculation_method' => 'unavailable',
                        'base_fee' => 0,
                        'distance_fee' => 0,
                        'zone_fee' => 0,
                        'was_free_delivery' => false,
                        'calculation_steps' => [],
                    ];
                }

                return [
                    'is_deliverable' => true,
                    'strategy_used' => 'coupon',
                    'calculation_method' => 'free',
                    'base_fee' => 0,
                    'distance_fee' => 0,
                    'zone_fee' => 0,
                    'surge_fee' => 0,
                    'final_charge' => 0,
                    'was_free_delivery' => true,
                    'calculation_steps' => ['Free delivery coupon used'],
                    'metadata' => array_merge($zoneResult['metadata'], ['reason' => 'coupon']),
                ];
            }
        }

        // Check for Referral Free Delivery Reward
        if ($user && $this->referralService->hasFreeDelivery($user)) {
             // Strict check: User must still be in a valid zone
             $zoneResult = $this->calculateZoneBased($store, $addressId, $distance);
             if (!$zoneResult) {
                 return [
                    'is_deliverable' => false,
                    'message' => 'Service not available in this area.',
                    'final_charge' => 0,
                    'strategy_used' => 'none',
                    'calculation_method' => 'unavailable',
                    'base_fee' => 0,
                    'distance_fee' => 0,
                    'zone_fee' => 0,
                    'was_free_delivery' => false,
                    'calculation_steps' => [],
                ];
             }

            return [
                'is_deliverable' => true,
                'strategy_used' => 'referral_reward',
                'calculation_method' => 'free',
                'base_fee' => 0,
                'distance_fee' => 0,
                'zone_fee' => 0,
                'surge_fee' => 0,
                'final_charge' => 0,
                'was_free_delivery' => true,
                'calculation_steps' => ['Free delivery reward from referral used'],
                'metadata' => array_merge($zoneResult['metadata'], ['reason' => 'referral_reward']),
            ];
        }

        // STRICT ZONE CHECK
        $zoneResult = $this->calculateZoneBased($store, $addressId, $distance);
        
        if ($zoneResult !== null) {
            return $zoneResult;
        }

        // If no zone match, strictly return unavailable
        return [
            'is_deliverable' => false,
            'message' => 'Service not available in this area.',
            'base_fee' => 0,
            'distance_fee' => 0,
            'zone_fee' => 0,
            'final_charge' => 0,
            'strategy_used' => 'none',
            'calculation_method' => 'unavailable',
            'was_free_delivery' => false,
            'calculation_steps' => ['No active delivery zone covers this address.'],
            'metadata' => [],
        ];
    }

    /**
     * Calculate zone-based delivery
     */
    private function calculateZoneBased(Store $store, int $addressId, ?float $distance): ?array
    {
        $address = Address::find($addressId);
        
        if (!$address || !$address->latitude || !$address->longitude) {
            return null;
        }

        // SPATIAL QUERY: Find zone covering the point
        $zone = Zone::active()
            ->covering($address->latitude, $address->longitude)
            ->first();

        if (!$zone) {
            return null;
        }

        // Check if store serves this zone
        $storeZone = $store->zones()
            ->wherePivot('zone_id', $zone->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$storeZone) {
            return null;
        }

        $baseFee = 0;
        $distanceFee = 0;
        $surgeFee = 0;
        $finalCharge = 0;
        $method = 'zone_distance';
        $steps = [];
        $surgeApplied = false;
        $surgeMessage = null;

        // Store override (Store specific fee for a zone)
        if ($storeZone->pivot->delivery_fee_override !== null) {
            $finalCharge = (float) $storeZone->pivot->delivery_fee_override;
            $baseFee = $finalCharge;
            $method = 'zone_override';
            $steps[] = "Store override for zone '{$zone->name}': " . \App\Helpers\CurrencyHelper::format($finalCharge);
        }
        // Standard Zone Calculation (Base + Distance + Surge)
        elseif ($distance !== null) {
            $feeDetails = $zone->calculateDeliveryFee($distance);
            
            $baseFee = $feeDetails['base_cost']; // This includes (Base + PerKM) from model method, or I can split it if I want distinct steps
            // Wait, model method returns: 'base_cost' (which is base + dist), 'surge_amount', 'total_cost'
            
            // Let's manually calculate for better step reporting
            $zoneBase = (float) $zone->base_delivery_fee;
            $zoneDistanceFee = $distance * (float) $zone->per_km_fee;
            $calculatedBase = $zoneBase + $zoneDistanceFee;
            
            // Surge
            $surgeFee = $feeDetails['surge_amount'];
            $surgeApplied = $feeDetails['surge_applied'];
            $surgeMessage = $feeDetails['surge_message'];
            
            $finalCharge = $calculatedBase + $surgeFee;
            
            $steps[] = "Zone base fee: " . \App\Helpers\CurrencyHelper::format($zoneBase);
            $steps[] = "Distance fee: {$distance}km × " . \App\Helpers\CurrencyHelper::format($zone->per_km_fee) . " = " . \App\Helpers\CurrencyHelper::format($zoneDistanceFee);
            
            if ($surgeApplied) {
                $steps[] = "Surge Pricing ({$surgeMessage}): + " . \App\Helpers\CurrencyHelper::format($surgeFee);
            }
            
            $steps[] = "Total: " . \App\Helpers\CurrencyHelper::format($finalCharge);
            
            $baseFee = $zoneBase;
            $distanceFee = $zoneDistanceFee;
        }
        // Fallback if distance is unknown (should rely on base only)
        else {
            $finalCharge = (float) $zone->base_delivery_fee;
            $baseFee = $finalCharge;
            $method = 'zone_base';
            $steps[] = "Zone base fee: " . \App\Helpers\CurrencyHelper::format($finalCharge);
        }

        return [
            'is_deliverable' => true,
            'strategy_used' => 'zone',
            'calculation_method' => $method,
            'zone_id' => $zone->id,
            'base_fee' => $baseFee,
            'distance_fee' => $distanceFee,
            'zone_fee' => 0, // Legacy field, kept for compatibility? Or maps to something else?
            'surge_fee' => $surgeFee,
            'final_charge' => $finalCharge,
            'was_free_delivery' => $finalCharge == 0,
            'calculation_steps' => $steps,
            'metadata' => [
                'zone_name' => $zone->name,
                'zone_city' => $zone->city,
                'surge_applied' => $surgeApplied,
                'surge_message' => $surgeMessage,
            ],
        ];
    }



    /**
     * Log calculation to database
     */
    private function logCalculation(array $data): ?int
    {
        try {
            $log = DeliveryCalculationLog::create($data);
            return $log->id;
        } catch (\Exception $e) {
            // Don't fail if logging fails
            Log::error('Failed to log delivery calculation', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(?int $storeId, ?int $addressId, float $subtotal, ?float $distance, ?string $couponCode = null, ?int $userId = null): string
    {
        $gen = (int) Cache::get('delivery_cache_gen', 0);

        return sprintf(
            'delivery_charge:v%d:%s:%s:%s:%s:%s:%s',
            $gen,
            $storeId ?? 'null',
            $addressId ?? 'null',
            number_format($subtotal, 2, '.', ''),
            $distance ? number_format($distance, 2, '.', '') : 'null',
            $couponCode ?? 'null',
            $userId ?? 'guest'
        );
    }

    /**
     * Clear delivery calculation cache for a specific store or address.
     * Uses a tag-prefix pattern to avoid flushing the entire cache.
     */
    public function clearCache(?int $storeId = null, ?int $addressId = null): void
    {
        // We can't enumerate all permutations, so we use a generation counter
        // that is embedded in every cache key. Bumping it effectively invalidates
        // all delivery cache entries without touching unrelated cache data.
        Cache::increment('delivery_cache_gen');
    }

    /**
     * Get delivery breakdown for display
     */
    public function getBreakdown(array $result): array
    {
        return [
            'strategy' => $result['strategy_used'],
            'method' => $result['calculation_method'],
            'base_fee' => $result['base_fee'],
            'distance_fee' => $result['distance_fee'],
            'zone_fee' => $result['zone_fee'],
            'total' => $result['final_charge'],
            'is_free' => $result['was_free_delivery'],
            'breakdown_text' => $this->generateBreakdownText($result),
            'steps' => $result['calculation_steps'] ?? [],
        ];
    }

    /**
     * Generate human-readable breakdown text
     */
    private function generateBreakdownText(array $result): string
    {
        if ($result['was_free_delivery']) {
            return 'Free Delivery';
        }

        $parts = [];
        
        if ($result['base_fee'] > 0) {
            $parts[] = "Base: " . \App\Helpers\CurrencyHelper::format($result['base_fee']);
        }
        
        if ($result['distance_fee'] > 0) {
            $parts[] = "Distance: " . \App\Helpers\CurrencyHelper::format($result['distance_fee']);
        }
        
        if ($result['zone_fee'] > 0) {
            $parts[] = "Zone: " . \App\Helpers\CurrencyHelper::format($result['zone_fee']);
        }
        
        $total = \App\Helpers\CurrencyHelper::format($result['final_charge']);
        
        return implode(' + ', $parts) . " = {$total}";
    }
}
