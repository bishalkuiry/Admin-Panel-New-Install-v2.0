<?php

namespace App\Services;

use App\Models\Tax;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class TaxService
{
    /**
     * Calculate total tax for a customer order.
     * Integrates Product-specific tax rates and Global/Order-level taxes.
     * 
     * @param  array  $items     Array of ['product_id', 'quantity', 'price', 'tax_rate', 'total', ...]
     * @param  float  $subtotal  Order subtotal before discount
     * @param  float  $discount  Discount amount
     * @param  int|null $storeId The store ID for the order
     * @return float  Total tax amount
     */
    public function calculateForOrder(array $items, float $subtotal, float $discount, ?int $storeId): float
    {
        // Check if all items are admin in-house (global) products
        if ($this->areAllGlobalProducts($items)) {
            return 0.0;
        }

        $totalTax = 0.0;
        $globalTaxableAmount = 0.0;

        // 1. Calculate Per-Item Tax (Product specific)
        foreach ($items as $item) {
            $itemTotal = $item['total'] ?? ($item['price'] * $item['quantity']);
            $productTaxRate = $item['tax_rate'] ?? 0;

            if ($productTaxRate > 0) {
                // Product-specific tax (assumes rate is percentage)
                $totalTax += $itemTotal * ($productTaxRate / 100);
            } else {
                // Determine item total that is subject to global taxes
                $globalTaxableAmount += $itemTotal;
            }
        }

        // 2. Apply Global/Order Level Taxes to remaining amount
        // Only if there is an amount that wasn't covered by product-specific taxes
        if ($globalTaxableAmount > 0) {
            $taxes = Cache::remember('active_customer_taxes', 300, function () {
                return Tax::active()->forCustomer()->get();
            });

            // Adjust taxable amount for discount
            // We apply the discount roughly proportionally to the global-taxable amount
            // This ensures we don't tax the discounted portion.
            $effectiveTaxable = max(0, $globalTaxableAmount - $discount);

            foreach ($taxes as $tax) {
                // Check order value thresholds (based on full order value usually)
                $orderValue = $subtotal - $discount;
                if ($tax->min_order_value !== null && $orderValue < $tax->min_order_value) continue;
                if ($tax->max_order_value !== null && $orderValue > $tax->max_order_value) continue;

                // Skip inclusive taxes for ADDITIVE tax calculation (they are already in price)
                if ($tax->is_inclusive) continue;

                $totalTax += $tax->calculate($effectiveTaxable);
            }
        }

        return round($totalTax, 2);
    }

    /**
     * Calculate Store Commission (unified logic).
     * Hierarchy: Product > Tax Rule (Store+Cat) > Tax Rule (Store) > Tax Rule (Global+Cat) > Tax Rule (Global) > Store Config
     *
     * @param Store $store
     * @param float $amount The amount to calculate commission on (item total)
     * @param Product|null $product The product being sold (for category/product-specific rules)
     * @return float Calculated commission amount
     */
    public function calculateCommission(Store $store, float $amount, ?Product $product = null): float
    {
        // 1. Product Override (Highest Priority)
        // If product has a specific commission rate set, it overrides everything.
        if ($product && !is_null($product->commission) && $product->commission > 0) {
            return round($amount * ($product->commission / 100), 2);
        }

        // 2. Rules Engine (Tax Table, applies_to='store')
        // We fetch all potential rules for this store (both specific and global ones)
        $rules = Cache::remember("commission_rules_store_{$store->id}", 300, function() use ($store) {
             return Tax::where('applies_to', 'store')
                 ->where(function ($q) use ($store) {
                     $q->where('store_id', $store->id)
                       ->orWhereNull('store_id');
                 })
                 ->where('is_active', true)
                 ->orderByDesc('store_id') // Prioritize specific store rules in collection
                 ->get();
        });

        $categoryId = $product?->category_id;
        
        // Find Best Match from Rules Collection (4-Level Hierarchy Logic)
        
        // A. Specific Store + Specific Category (Level 2)
        $rule = $rules->first(fn($r) => $r->store_id == $store->id && $r->store_category_id == $categoryId);
        
        // B. Specific Store (Level 3)
        if (!$rule) {
            $rule = $rules->first(fn($r) => $r->store_id == $store->id && is_null($r->store_category_id));
        }
        
        // C. Global (Level 4 - No store, No category)
        if (!$rule) {
            $rule = $rules->first(fn($r) => is_null($r->store_id) && is_null($r->store_category_id));
        }

        if ($rule) {
            return $rule->calculate($amount);
        }
        
        // 3. Fallback to Legacy Store Config
        // If no new rules exist, check if the store has a legacy percentage configured
        if ($store->commission_percent > 0) {
             return $amount * ($store->commission_percent / 100);
        }

        // 4. Global Settings Fallback (Legacy)
        // Finally, check general settings
        $globalPercent = (float) \App\Models\Setting::get('default_commission_percent', 0);
        if ($globalPercent > 0) {
            return $amount * ($globalPercent / 100);
        }
        
        return 0.0;
    }

    /**
     * Calculate delivery partner taxes/commissions.
     */
    public function calculateForPartner(float $deliveryFee): float
    {
        $taxes = Cache::remember('active_partner_taxes', 300, function () {
            return Tax::active()->where('applies_to', 'delivery_partner')->get();
        });

        $totalTax = 0.0;
        foreach ($taxes as $tax) {
            $totalTax += $tax->calculate($deliveryFee);
        }

        return round($totalTax, 2);
    }

    /**
     * Invalidate the tax cache.
     */
    public function clearCache(): void
    {
        Cache::forget('active_customer_taxes');
        Cache::forget('active_partner_taxes');
        
        // Clear all store commission rule caches
        // Note: In high-scale systems, use tags or more targeted clearing.
        Store::pluck('id')->each(function ($storeId) {
            Cache::forget("commission_rules_store_{$storeId}");
        });
        
        // Clear legacy cache keys just in case
        Tax::pluck('store_id')->unique()->each(function ($id) {
            if ($id) Cache::forget("active_store_taxes_{$id}_all"); 
        });
        Category::pluck('id')->each(fn($id) => Cache::forget("active_store_taxes_all_{$id}"));
    }

    private function areAllGlobalProducts(array $items): bool
    {
        $productIds = array_column($items, 'product_id');
        if (empty($productIds)) {
            return false;
        }

        $storeProducts = Product::whereIn('id', $productIds)
            ->whereNotNull('store_id')
            ->count();

        return $storeProducts === 0;
    }
}
