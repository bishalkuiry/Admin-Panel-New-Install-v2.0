<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private DeliveryCalculationService $deliveryCalculationService,
        private TaxService $taxService
    ) {}

    // -------------------------------------------------------------------------
    // Cache helpers
    // -------------------------------------------------------------------------

    /**
     * Cache key for a user's cart.
     */
    private function cartCacheKey(User $user): string
    {
        return "cart:user:{$user->id}";
    }

    /**
     * Bust the cart cache after any mutation so the next read is fresh.
     */
    private function invalidateCartCache(User $user): void
    {
        Cache::forget($this->cartCacheKey($user));
    }

    /**
     * Whether single-store cart enforcement is enabled.
     *
     * Cached for 5 minutes so we don't hit the DB on every addItem() call.
     * The cache is busted automatically when the setting changes via the
     * admin panel (or can be cleared manually with Cache::forget).
     */
    private function isSingleStoreCart(): bool
    {
        return (bool) Cache::remember('setting:single_store_cart', 300, function () {
            return Setting::get('single_store_cart', '1');
        });
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Get or create cart for user.
     *
     * We cache the cart as a plain array (via toArray()) and re-hydrate it
     * into a fresh Eloquent model on every cache hit. Caching the Eloquent
     * model object directly is unsafe: accessor state, lazy-loaded relation
     * proxies, and model events do not survive serialization cleanly across
     * cache TTLs, which can cause silent data corruption.
     *
     * Re-hydration strategy:
     *   - Cart attributes are filled directly.
     *   - items, items.product, items.product.primaryImage, and store
     *     relations are reconstructed from the cached array so no DB query
     *     fires on a cache hit.
     *
     * TTL: 15 seconds. Short enough that stale reads across sessions are
     * bounded to 15 s, while still absorbing burst reads within a single
     * request (e.g. getCart() called by both getCartSummary() and the
     * response builder in the same HTTP cycle). Every mutation also calls
     * invalidateCartCache() to bust it immediately.
     */
    public function getCart(User $user): Cart
    {
        $cacheKey = $this->cartCacheKey($user);

        $cartArray = Cache::remember($cacheKey, 15, function () use ($user) {
            $cart = Cart::with(['items.product.primaryImage', 'items.product', 'store'])
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['session_id' => null]
                );
            // Serialize to a plain array so the cache driver stores safe,
            // serializable data rather than an Eloquent model object.
            return $cart->toArray();
        });

        return $this->hydrateCartFromArray($cartArray);
    }

    /**
     * Re-hydrate a Cart Eloquent model (with relations) from a plain array.
     *
     * This is the counterpart to the toArray() serialization in getCart().
     * It reconstructs the full object graph so callers can use $cart->items,
     * $cart->store, etc. exactly as if the model had been loaded from the DB.
     */
    private function hydrateCartFromArray(array $data): Cart
    {
        $cart = new Cart();
        $cart->forceFill(array_diff_key($data, array_flip(['items', 'store'])));
        $cart->exists = true;

        // Hydrate store relation
        if (!empty($data['store'])) {
            $store = new \App\Models\Store();
            $store->forceFill($data['store']);
            $store->exists = true;
            $cart->setRelation('store', $store);
        } else {
            $cart->setRelation('store', null);
        }

        // Hydrate items relation (with nested product + primaryImage)
        $items = collect($data['items'] ?? [])->map(function (array $itemData) {
            $item = new CartItem();
            $item->forceFill(array_diff_key($itemData, array_flip(['product'])));
            $item->exists = true;

            if (!empty($itemData['product'])) {
                $productData = $itemData['product'];
                $product = new \App\Models\Product();
                $product->forceFill(array_diff_key($productData, array_flip(['primary_image'])));
                $product->exists = true;

                // Hydrate primaryImage relation
                if (!empty($productData['primary_image'])) {
                    $image = new \App\Models\ProductImage();
                    $image->forceFill($productData['primary_image']);
                    $image->exists = true;
                    $product->setRelation('primaryImage', $image);
                } else {
                    $product->setRelation('primaryImage', null);
                }

                $item->setRelation('product', $product);
            }

            return $item;
        });

        $cart->setRelation('items', $items);

        return $cart;
    }

    // -------------------------------------------------------------------------
    // Mutations
    // -------------------------------------------------------------------------

    /**
     * Set a cart item to an exact quantity (replace strategy for sync).
     *
     * Unlike addItem() which increments, this method sets the quantity to
     * exactly $quantity regardless of what is already in the cart.
     * Used by the batch sync endpoint when merge_strategy=replace.
     *
     * NOTE: The cache is intentionally NOT used here — we always read fresh
     * from the DB so that sequential calls within the same sync batch see
     * each other's writes. The caller (CartController::sync) is responsible
     * for calling invalidateCartCache() once after the entire batch completes.
     *
     * @param int|null $variantId  Optional product variant ID.
     */
    public function setItemQuantity(User $user, int $productId, int $quantity, ?int $variantId = null): Cart
    {
        // Bypass cache — read directly from DB so we see writes from earlier
        // items in the same sync batch.
        $cart = Cart::with(['items.product.primaryImage', 'store'])
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );

        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw new \Exception('Product is not available');
        }

        // Resolve price and stock from variant when provided.
        $price = $product->price;
        if ($variantId !== null) {
            $variant = \App\Models\ProductVariant::where('product_id', $product->id)
                ->findOrFail($variantId);
            $price = $variant->selling_price ?? $variant->mrp ?? $product->price;
            if ($variant->quantity < $quantity) {
                throw new \Exception("Insufficient stock for variant");
            }
        } elseif ($product->quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        if ($this->isSingleStoreCart() && $cart->items->isNotEmpty()) {
            $cartStoreId = $cart->items->first()->product->store_id;
            if ($product->store_id != $cartStoreId) {
                throw new \Exception('Cannot add items from different stores. Please clear your cart first.');
            }
        }

        /** @var CartItem|null $existingItem */
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $quantity,
                'price'    => $price,
            ]);
        } else {
            $isFirstItem = $cart->items->isEmpty();
            $cart->items()->create([
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'quantity'           => $quantity,
                'price'              => $price,
            ]);
            if ($isFirstItem) {
                $cart->update(['store_id' => $product->store_id]);
            }
        }

        $this->updateCartTotals($cart);
        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])->findOrFail($cart->id);
    }

    /**
     * Add item to cart.
     *
     * Wrapped in a DB transaction with a pessimistic lock on the cart row.
     * This eliminates the read-then-write race condition where two concurrent
     * addItem() calls both read the same cached snapshot, both see
     * isFirstItem=true, and both attempt to set store_id — or both pass the
     * single-store check against stale data.
     *
     * The lock serialises concurrent mutations for the same user's cart.
     * The cache is bypassed for the read inside the transaction (lockForUpdate
     * requires a real DB row) and busted after the transaction commits.
     *
     * NOTE: When called from the batch sync loop the cache has already been
     * invalidated before the loop starts (see CartController::sync). Each call
     * here busts the cache again at the end, so the next iteration always
     * reads the latest DB state.
     *
     * @param int|null $variantId  Optional product variant ID.
     */
    public function addItem(User $user, int $productId, int $quantity = 1, ?int $variantId = null, ?array $options = null): Cart
    {
        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw new \Exception('Product is not available');
        }

        // Resolve price and stock from variant when provided.
        // Done outside the transaction — read-only, no lock needed.
        $price = $product->price;
        if ($variantId !== null) {
            $variant = \App\Models\ProductVariant::where('product_id', $product->id)
                ->findOrFail($variantId);
            $price = $variant->selling_price ?? $variant->mrp ?? $product->price;
            if ($variant->quantity < $quantity) {
                throw new \Exception("Insufficient stock for variant");
            }
        } elseif ($product->quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        DB::transaction(function () use ($user, $product, $productId, $quantity, $variantId, $price, $options) {
            // Pessimistic lock — serialises concurrent addItem() calls for
            // this user. firstOrCreate is safe here: if no row exists the
            // INSERT is atomic; if it does exist lockForUpdate holds it.
            $cart = Cart::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );

            // Load relations fresh from DB (bypasses cache — we're inside a
            // transaction and need the authoritative state).
            $cart->load(['items.product', 'store']);

            if ($this->isSingleStoreCart() && $cart->items->isNotEmpty()) {
                $cartStoreId = $cart->items->first()->product->store_id ?? null;
                if ($product->store_id != $cartStoreId) {
                    throw new \Exception('Cannot add items from different stores. Please clear your cart first.');
                }
            }

            /** @var CartItem|null $existingItem */
            $existingItem = $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;

                // Stock check against the correct source (variant or product).
                $availableQty = $variantId !== null
                    ? (\App\Models\ProductVariant::find($variantId)?->quantity ?? 0)
                    : $product->quantity;

                if ($availableQty < $newQuantity) {
                    throw new \Exception('Cannot add more items. Stock limit reached.');
                }

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'price'    => $price,
                    'options'  => $options ?? $existingItem->options,
                ]);
            } else {
                // Capture whether this is the first item BEFORE creating
                // (in-memory collection is stale after create).
                $isFirstItem = $cart->items->isEmpty();

                $cart->items()->create([
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'quantity'           => $quantity,
                    'price'              => $price,
                    'options'            => $options,
                ]);

                if ($isFirstItem) {
                    $cart->update(['store_id' => $product->store_id]);
                }
            }

            $this->updateCartTotals($cart);
        });

        // Bust cache after the transaction commits so the next read is fresh.
        $this->invalidateCartCache($user);

        // Return a fresh load — single DB query with all relations.
        $cart = Cart::with(['items.product.primaryImage', 'store'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return $cart;
    }

    /**
     * Update item quantity.
     *
     * Wrapped in a DB transaction with a pessimistic lock on the cart row to
     * prevent concurrent updates from racing on the same cart state.
     *
     * The item's stored price is intentionally NOT updated here. The price
     * column captures the price at the time the item was added to the cart.
     * Updating it on every quantity change would silently break the
     * CartItem::getSubtotalAttribute() guarantee and the pre-checkout
     * price-drift detection in validateCartItems(). Price drift is surfaced
     * explicitly to the customer before payment — not silently corrected.
     */
    public function updateItem(User $user, int $itemId, int $quantity): Cart
    {
        if ($quantity <= 0) {
            return $this->removeItem($user, $itemId);
        }

        DB::transaction(function () use ($user, $itemId, $quantity) {
            // Lock the cart row — serialises concurrent updateItem() calls.
            $cart = Cart::lockForUpdate()
                ->where('user_id', $user->id)
                ->firstOrFail();

            $item = $cart->items()->findOrFail($itemId);

            // Check stock against the correct source: variant when present, product otherwise.
            if ($item->product_variant_id !== null) {
                $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                if ($variant && $variant->quantity < $quantity) {
                    throw new \Exception('Insufficient stock for this variant');
                }
            } else {
                // Load product fresh — the cached cart's product may be stale.
                $product = Product::findOrFail($item->product_id);
                if ($product->quantity < $quantity) {
                    throw new \Exception('Insufficient stock');
                }
            }

            // Only update quantity — preserve the original cart price so that
            // price-drift detection in validateCartItems() remains accurate.
            $item->update(['quantity' => $quantity]);

            $this->updateCartTotals($cart);
        });

        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    /**
     * Remove item from cart.
     *
     * Wrapped in a DB transaction with a pessimistic lock on the cart row to
     * prevent a concurrent addItem() or updateItem() from reading a snapshot
     * that still includes the item being removed.
     */
    public function removeItem(User $user, int $itemId): Cart
    {
        DB::transaction(function () use ($user, $itemId) {
            // Lock the cart row — serialises concurrent mutations.
            $cart = Cart::lockForUpdate()
                ->where('user_id', $user->id)
                ->firstOrFail();

            $cart->items()->where('id', $itemId)->delete();

            if ($cart->items()->count() === 0) {
                $cart->update([
                    'store_id'    => null,
                    'subtotal'    => 0,
                    'discount'    => 0,
                    'total'       => 0,
                    'coupon_code' => null,
                    'coupon_id'   => null,
                ]);
            } else {
                $this->updateCartTotals($cart);
            }
        });

        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    /**
     * Clear cart.
     */
    public function clearCart(User $user): Cart
    {
        $cart = $this->getCart($user);
        $cart->items()->delete();

        $cart->update([
            'store_id'    => null,
            'subtotal'    => 0,
            'discount'    => 0,
            'total'       => 0,
            'coupon_code' => null,
            'coupon_id'   => null,
        ]);

        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])->findOrFail($cart->id);
    }

    /**
     * Apply coupon to cart.
     */
    public function applyCoupon(User $user, string $couponCode): Cart
    {
        $cart = $this->getCart($user);

        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            throw new \Exception('Invalid coupon code');
        }

        if (!$coupon->canBeUsedBy($user)) {
            throw new \Exception('This coupon cannot be used');
        }

        // Compute subtotal from the already-loaded items relation.
        // getCart() eager-loads items, so no extra query needed here.
        $subtotal = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

        $discount = $coupon->calculateDiscount($subtotal);

        $cart->update([
            'coupon_id'   => $coupon->id,
            'coupon_code' => $couponCode,
            'discount'    => $discount,
            'total'       => max(0, $subtotal - $discount),
        ]);

        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])->findOrFail($cart->id);
    }

    /**
     * Remove coupon from cart.
     */
    public function removeCoupon(User $user): Cart
    {
        $cart = $this->getCart($user);

        $cart->update([
            'coupon_id'   => null,
            'coupon_code' => null,
            'discount'    => 0,
            'total'       => $cart->subtotal ?? 0,
        ]);

        $this->invalidateCartCache($user);

        return Cart::with(['items.product.primaryImage', 'store'])->findOrFail($cart->id);
    }

    // -------------------------------------------------------------------------
    // Summary & validation
    // -------------------------------------------------------------------------

    /**
     * Get cart summary.
     */
    public function getCartSummary(User $user, ?float $distance = null, ?int $addressId = null): array
    {
        $cart = $this->getCart($user);

        $deliveryResult   = $this->calculateDeliveryCharge($cart, $distance, $addressId, $user->id);
        $deliveryCharge   = $deliveryResult['final_charge'];
        $deliveryBreakdown = $this->deliveryCalculationService->getBreakdown($deliveryResult);

        $items = $cart->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->price,
                'tax_rate'   => $item->product->tax_rate ?? 0,
                'total'      => $item->price * $item->quantity,
            ];
        })->toArray();

        $subtotal = $cart->subtotal;
        $discount = (float) ($cart->attributes['discount'] ?? 0);
        $tax      = $this->taxService->calculateForOrder($items, $subtotal, $discount, $cart->store_id);

        return [
            'items_count'        => $cart->items->sum('quantity'),
            'unique_items'       => $cart->items->count(),
            'subtotal'           => $subtotal,
            'discount'           => $discount,
            'tax'                => $tax,
            'delivery_charge'    => $deliveryCharge,
            'delivery_breakdown' => $deliveryBreakdown,
            'total'              => $subtotal - $discount + $tax,
            'total_with_delivery'=> $subtotal - $discount + $tax + $deliveryCharge,
            'coupon_code'        => $cart->coupon_code,
        ];
    }

    /**
     * Validate all items in the cart for stock availability and price changes.
     * Returns an array of issue descriptors; empty array means cart is clean.
     *
     * @return array<int, array{product_id: int, name: string, type: string, message: string}>
     */
    public function validateCartItems(User $user): array
    {
        $cart   = $this->getCart($user);
        $issues = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'name'       => $product?->name ?? 'Unknown product',
                    'type'       => 'unavailable',
                    'message'    => 'This product is no longer available.',
                ];
                continue;
            }

            $variant = null;
            if ($item->product_variant_id !== null) {
                $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                if (!$variant || !$variant->is_active) {
                    $issues[] = [
                        'product_id' => $item->product_id,
                        'name'       => $product->name,
                        'type'       => 'unavailable',
                        'message'    => 'This variation is no longer available.',
                    ];
                    continue;
                }
            }

            $availableStock = $variant ? $variant->quantity : $product->quantity;
            if ($availableStock < $item->quantity) {
                $issues[] = [
                    'product_id'    => $item->product_id,
                    'name'          => $product->name,
                    'type'          => 'out_of_stock',
                    'message'       => "Only {$availableStock} unit(s) available.",
                    'available_qty' => $availableStock,
                    'requested_qty' => $item->quantity,
                ];
            }

            $expectedPrice = $variant ? ($variant->selling_price ?? $variant->mrp ?? $product->price) : $product->price;
            if (abs((float) $item->price - (float) $expectedPrice) > 0.01) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'name'       => $product->name,
                    'type'       => 'price_changed',
                    'message'    => 'The price of this item has changed.',
                    'cart_price' => (float) $item->price,
                    'live_price' => (float) $expectedPrice,
                ];
            }
        }

        return $issues;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate delivery charge for cart items.
     */
    private function calculateDeliveryCharge(Cart $cart, ?float $distance, ?int $addressId, ?int $userId): array
    {
        if ($cart->items->isEmpty()) {
            return [
                'strategy_used'     => 'none',
                'calculation_method'=> 'empty_cart',
                'base_fee'          => 0,
                'distance_fee'      => 0,
                'zone_fee'          => 0,
                'final_charge'      => 0,
                'was_free_delivery' => false,
                'calculation_steps' => ['Empty cart'],
                'metadata'          => [],
            ];
        }

        $storeId  = $cart->items->first()->product->store_id ?? null;
        $subtotal = $cart->subtotal;

        return $this->deliveryCalculationService->calculate([
            'store_id'    => $storeId,
            'address_id'  => $addressId,
            'subtotal'    => $subtotal,
            'distance'    => $distance,
            'user_id'     => $userId,
            'cart_id'     => $cart->id,
            'coupon_code' => $cart->coupon_code,
        ]);
    }

    /**
     * Recalculate and persist cart subtotal, discount, and total.
     *
     * Callers always pass a cart that was just mutated via the DB (create /
     * update / delete on cart_items), so we reload only the items relation
     * rather than the full cart to get accurate quantities/prices without
     * re-fetching the store and product images unnecessarily.
     */
    private function updateCartTotals(Cart $cart): void
    {
        // Reload only items.product — we don't need primaryImage here.
        $cart->load('items.product');

        $subtotal = $cart->items->sum(fn ($item) => ($item->price ?? $item->product->price) * $item->quantity);

        $discount = 0;
        if ($cart->coupon_id) {
            $coupon = Coupon::find($cart->coupon_id);
            if ($coupon && $coupon->isValid()) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                // Coupon expired or deleted — silently remove it.
                $cart->coupon_id   = null;
                $cart->coupon_code = null;
            }
        }

        $cart->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => $subtotal - $discount,
        ]);
    }
}
