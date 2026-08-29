<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    /**
     * Get cart
     */
    public function index(Request $request)
    {
        $distance  = $request->query('distance', null);
        $addressId = $request->query('address_id', null);

        return $this->cartResponse($request->user(), null, $distance, $addressId);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
            'variant_id' => 'nullable|exists:product_variants,id',
            'options'    => 'nullable|array',
        ]);

        try {
            $this->cartService->addItem(
                $request->user(),
                $request->input('product_id'),
                $request->input('quantity'),
                $request->input('variant_id'),
                $request->input('options')
            );

            return $this->cartResponse(
                $request->user(),
                'Item added to cart',
                $request->input('distance'),
                $request->input('address_id')
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update item quantity
     */
    public function updateItem(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        try {
            $this->cartService->updateItem(
                $request->user(),
                $itemId,
                $request->input('quantity')
            );

            return $this->cartResponse(
                $request->user(),
                'Cart updated',
                $request->input('distance'),
                $request->input('address_id')
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, int $itemId)
    {
        $this->cartService->removeItem($request->user(), $itemId);

        return $this->cartResponse(
            $request->user(),
            'Item removed from cart',
            $request->input('distance'),
            $request->input('address_id')
        );
    }

    /**
     * Clear cart
     */
    public function clear(Request $request)
    {
        $this->cartService->clearCart($request->user());

        return $this->cartResponse(
            $request->user(),
            'Cart cleared',
            $request->input('distance'),
            $request->input('address_id')
        );
    }

    /**
     * Apply coupon
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        try {
            $this->cartService->applyCoupon($request->user(), $request->input('coupon_code'));

            return $this->cartResponse(
                $request->user(),
                'Coupon applied',
                $request->input('distance'),
                $request->input('address_id')
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove coupon
     */
    public function removeCoupon(Request $request)
    {
        $this->cartService->removeCoupon($request->user());

        return $this->cartResponse(
            $request->user(),
            'Coupon removed',
            $request->input('distance'),
            $request->input('address_id')
        );
    }

    /**
     * Batch sync — accepts an array of items and upserts them into the cart.
     * Used by the mobile app to migrate guest-cart items after login in a
     * single round-trip instead of N sequential POST /cart/items calls.
     *
     * POST /api/v1/cart/sync
     * Body: {
     *   "items": [{ "product_id": 1, "quantity": 2 }, ...],
     *   "merge_strategy": "additive" | "replace"   (default: "additive")
     * }
     *
     * merge_strategy:
     *   additive — adds guest quantities on top of any existing server qty.
     *              Used when the user may have items on both sides.
     *   replace  — sets the quantity to exactly the guest value, overwriting
     *              any existing server qty for that product.
     *              Used when the guest cart is the authoritative source of truth
     *              (e.g. the user had no prior server cart).
     */
    public function sync(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:100',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'merge_strategy'     => 'nullable|in:additive,replace',
        ]);

        $strategy = $request->input('merge_strategy', 'additive');
        $errors   = [];

        // Bust the cart cache BEFORE the loop so that every addItem() /
        // setItemQuantity() call within this request reads the latest DB state
        // rather than a 60-second stale snapshot.  Without this, items 2-N in
        // the batch all see the pre-sync cart and can overwrite each other's
        // writes.
        \Illuminate\Support\Facades\Cache::forget("cart:user:{$request->user()->id}");

        foreach ($request->input('items') as $index => $item) {
            try {
                if ($strategy === 'replace') {
                    // For replace strategy: set the cart item to exactly the
                    // guest quantity rather than adding on top of server qty.
                    $this->cartService->setItemQuantity(
                        $request->user(),
                        $item['product_id'],
                        $item['quantity'],
                        $item['variant_id'] ?? null
                    );
                } else {
                    // Default additive: increment existing qty or create new.
                    $this->cartService->addItem(
                        $request->user(),
                        $item['product_id'],
                        $item['quantity'],
                        $item['variant_id'] ?? null
                    );
                }
            } catch (\Exception $e) {
                // Collect per-item errors but continue processing the rest.
                $errors[] = [
                    'index'      => $index,
                    'product_id' => $item['product_id'],
                    'message'    => $e->getMessage(),
                ];
            }
        }

        return $this->cartResponse(
            $request->user(),
            count($errors) === 0 ? 'Cart synced' : 'Cart partially synced',
            null,
            null,
            $errors
        );
    }

    /**
     * Validate cart items before checkout — returns stock/price issues
     * without modifying the cart.
     *
     * GET /api/v1/cart/validate
     */
    public function validate(Request $request)
    {
        $issues = $this->cartService->validateCartItems($request->user());

        return response()->json([
            'success' => true,
            'data'    => [
                'is_valid' => empty($issues),
                'issues'   => $issues,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build and return the full cart JSON response.
     *
     * @param User         $user
     * @param string|null  $message
     * @param float|null   $distance   km, for per-km delivery calculation
     * @param int|null     $addressId  for zone-based delivery calculation
     * @param array        $syncErrors per-item errors from batch sync
     */
    private function cartResponse(
        User $user,
        ?string $message = null,
        ?float $distance = null,
        ?int $addressId = null,
        array $syncErrors = []
    ) {
        $cart = $this->cartService->getCart($user);

        $response = [
            'success' => true,
            'message' => $message,
            'data'    => [
                'store_id' => $cart->store_id,
                'store'    => $cart->store ? [
                    'id'               => $cart->store->id,
                    'name'             => $cart->store->name,
                    'logo'             => storage_url($cart->store->logo),
                    'address'          => $cart->store->address,
                    'preparation_time' => $cart->store->preparation_time,
                    'delivery_enabled' => (bool) ($cart->store->delivery_enabled ?? true),
                    'pickup_enabled'   => (bool) ($cart->store->pickup_enabled ?? true),
                ] : null,
                'items'           => $cart->items->map(fn ($item) => $this->formatItem($item)),
                'summary'         => $this->cartService->getCartSummary($user, $distance, $addressId),
                'coupon_code'     => $cart->coupon_code,
                'coupon_discount' => $cart->discount,
            ],
        ];

        if (!empty($syncErrors)) {
            $response['sync_errors'] = $syncErrors;
        }

        return response()->json($response);
    }

    private function formatItem($item): array
    {
        return [
            'id'                       => $item->id,
            'product_id'               => $item->product_id,
            'variant_id'               => $item->product_variant_id,
            'is_prescription_required' => (bool) $item->product->is_prescription_required,
            'product'                  => [
                'id'                       => $item->product->id,
                'name'                     => $item->product->name,
                'sku'                      => $item->product->sku,
                'price'                    => $item->product->price,
                'compare_price'            => $item->product->compare_price,
                'store_id'                 => $item->product->store_id,
                'image'                    => storage_url($item->product->primaryImage?->image),
                'in_stock'                 => $item->product->quantity > 0,
                'available_quantity'       => $item->product->quantity,
                'category_id'              => $item->product->category_id,
                'is_prescription_required' => (bool) $item->product->is_prescription_required,
                'health_info'              => [
                    'is_prescription_required' => (bool) $item->product->is_prescription_required,
                ],
            ],
            'quantity' => $item->quantity,
            'price'    => $item->price,
            'total'    => $item->price * $item->quantity,
        ];
    }
}
