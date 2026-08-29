<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CartService $cartService
    ) {
    }

    /**
     * List user orders with ETag support
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // BACKGROUND SHIELD: Cache the user's order list for 30 seconds
        $cacheKey = "orders_user_{$user->id}_p{$page}";

        $cachedData = Cache::remember($cacheKey, 30, function () use ($user, $perPage) {
            // Get light order IDs for ETag calculation
            $allOrders = Order::where('user_id', $user->id)
                ->select('id', 'order_number', 'status', 'updated_at')
                ->latest()
                ->get();

            $count = $allOrders->count();
            $maxUpdated = $allOrders->max('updated_at')?->timestamp ?? 0;
            $orderNumbers = $allOrders->pluck('order_number')->implode(',');

            $etag = 'orders_' . md5("{$user->id}_{$count}_{$maxUpdated}_{$orderNumbers}");

            // Now get paginated data for the cache
            $orders = Order::where('user_id', $user->id)
                ->with(['items.product.primaryImage', 'address'])
                ->latest()
                ->paginate($perPage);

            return [
                'etag' => $etag,
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'version' => $etag,
                ],
            ];
        });

        $etag = $cachedData['etag'];

        // Check if client has latest version
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()->json([
            'success' => true,
            'data' => $cachedData['data'],
            'meta' => $cachedData['meta'],
        ])->header('ETag', $etag)
            ->header('Cache-Control', 'private, max-age=10'); // Short client TTL, long server shield
    }

    /**
     * Create order
     */
    public function store(OrderRequest $request)
    {
        try {
            $validated = $request->validated();
            $user      = $request->user();

            if ($request->hasFile('prescription')) {
                $validated['prescription_image'] = app(\App\Services\StorageService::class)->store($request->file('prescription'), 'prescriptions');
            } elseif ($request->hasFile('prescription_image')) {
                $validated['prescription_image'] = app(\App\Services\StorageService::class)->store($request->file('prescription_image'), 'prescriptions');
            }

            // ── Idempotency guard ────────────────────────────────────────────
            // If the client sends an idempotency_key, check whether we already
            // processed this exact request (network retry / double-tap).
            //
            // The cache key includes a fingerprint of the items array so that
            // a different cart with the same idempotency_key (e.g. the client
            // reused a key across sessions) is treated as a new request rather
            // than returning the stale cached response.
            if (!empty($validated['idempotency_key'])) {
                $itemsFingerprint = md5(json_encode(
                    collect($validated['items'] ?? [])
                        ->map(fn($i) => [
                            'p' => $i['product_id'],
                            'q' => $i['quantity'],
                            'v' => $i['variant_id'] ?? null,
                        ])
                        ->sortBy('p')
                        ->values()
                        ->all()
                ));
                $idemCacheKey = "order_idem:{$user->id}:{$validated['idempotency_key']}:{$itemsFingerprint}";
                $cached = Cache::get($idemCacheKey);
                if ($cached !== null) {
                    // Return the previously-built response verbatim.
                    return response()->json($cached, 201);
                }
            }
            // ────────────────────────────────────────────────────────────────

            Log::info('OrderController: store called', [
                'user_id'      => $user->id,
                'item_count'   => count($validated['items'] ?? []),
                'items'        => $validated['items'] ?? [],
                'raw_items'    => $request->input('items'),
            ]);
            $result = $this->orderService->create($user, $validated);

            // Handle single or multiple orders with consistent response structure.
            //
            // is_multi_order is TRUE only when more than one order was actually
            // created (items from multiple stores). When all items belong to one
            // store — even in multi-vendor mode — a single Order is returned and
            // is_multi_order must be FALSE so the client navigates correctly.
            if (is_array($result) && count($result) > 1) {
                // Genuinely multiple orders (items from different stores)
                $responseBody = [
                    'success' => true,
                    'message' => count($result) . ' orders placed successfully',
                    'data' => [
                        'is_multi_order' => true,
                        'orders' => OrderResource::collection($result),
                        'order_count' => count($result),
                        'primary_order' => new OrderResource($result[0]),
                    ],
                ];
            } elseif (is_array($result)) {
                // Multi-vendor mode returned an array but all items were from
                // one store — unwrap to a single-order response so the client
                // treats it identically to single-vendor mode.
                $singleOrder = $result[0];
                $responseBody = [
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'data' => [
                        'is_multi_order' => false,
                        'order' => new OrderResource($singleOrder),
                        'order_count' => 1,
                    ],
                ];
            } else {
                // Single-vendor: one order created
                $responseBody = [
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'data' => [
                        'is_multi_order' => false,
                        'order' => new OrderResource($result),
                        'order_count' => 1,
                    ],
                ];
            }

            // Store response for idempotency replay (5 minutes).
            if (!empty($validated['idempotency_key'])) {
                Cache::put($idemCacheKey, $responseBody, 300);
            }

            return response()->json($responseBody, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    //Get order details

    public function show(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->load(['items.product.primaryImage', 'address']);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    //Cancel order

    public function cancel(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->orderService->cancel($order, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * One-Click Reorder: Copy items from previous order into current cart
     */
    public function reorder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or unauthorized',
            ], 404);
        }

        $order->load(['items.product', 'items.variant']);

        $user = $request->user();
        $cart = $this->cartService->getOrCreateCart($user);

        $addedCount = 0;
        foreach ($order->items as $item) {
            if ($item->product && $item->product->is_active) {
                $this->cartService->addItem(
                    $cart,
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity
                );
                $addedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$addedCount} items added to your cart for reorder",
            'data' => [
                'cart' => $cart->fresh(['items.product.primaryImage']),
            ],
        ]);
    }

    // Track order

    public function track(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => [
                    'value' => $order->status->value,
                    'label' => $order->status->label(),
                    'color' => $order->status->color(),
                ],
                'timeline' => $this->getOrderTimeline($order),
                'estimated_delivery' => $order->estimated_delivery_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Get order timeline
     */
    private function getOrderTimeline(Order $order): array
    {
        $timeline = [
            [
                'status' => 'pending',
                'label' => 'Order Placed',
                'completed' => true,
                'date' => $order->created_at->toISOString(),
            ],
            [
                'status' => 'confirmed',
                'label' => 'Order Confirmed',
                'completed' => in_array($order->status->value, ['confirmed', 'packed', 'picked_up', 'out_for_delivery', 'delivered']),
                'date' => $order->confirmed_at?->toISOString(),
            ],
            [
                'status' => 'packed',
                'label' => 'Packed',
                'completed' => in_array($order->status->value, ['packed', 'picked_up', 'out_for_delivery', 'delivered']),
                'date' => $order->packed_at?->toISOString(),
            ],
            [
                'status' => 'picked_up',
                'label' => 'Picked Up',
                'completed' => in_array($order->status->value, ['picked_up', 'out_for_delivery', 'delivered']),
                'date' => $order->picked_up_at?->toISOString(),
            ],
            [
                'status' => 'out_for_delivery',
                'label' => 'Out for Delivery',
                'completed' => in_array($order->status->value, ['out_for_delivery', 'delivered']),
                'date' => $order->out_for_delivery_at?->toISOString(),
            ],
            [
                'status' => 'delivered',
                'label' => 'Delivered',
                'completed' => $order->status->value === 'delivered',
                'date' => $order->delivered_at?->toISOString(),
            ],
        ];

        return $timeline;
    }
}
