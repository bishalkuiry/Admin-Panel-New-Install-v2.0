<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Services\RealtimeService;
use App\Services\OrderService;
use App\Services\OrderChatService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private RealtimeService $realtimeService,
        private OrderChatService $chatService
    ) {}

    /**
     * List store orders
     */
    public function index(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $query = $store->orders()->with(['user', 'items.product.primaryImage', 'address', 'deliveryPartner']);

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->query('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $orders = $query->latest()->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }


    /**
     * Get order details
     */
    public function show(Request $request, int $orderId)
    {
        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->with(['user', 'items.product.primaryImage', 'address', 'deliveryPartner'])->findOrFail($orderId);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, int $orderId)
    {
        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->findOrFail($orderId);

        // Requirement #8: Store cannot set delivery statuses (out_for_delivery, delivered)
        $request->validate([
            'status' => 'required|in:confirmed,processing,packed,cancelled',
        ]);

        $statusStr = $request->input('status');

        // Requirement #20: Check store cancellation setting
        if ($statusStr === 'cancelled') {
            $storeCancelEnabled = \App\Models\Setting::get('store_cancellation_enabled', '1');
            if ($storeCancelEnabled !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Store cancellation is disabled by admin.',
                ], 403);
            }
        }

        try {
            $newStatus = OrderStatus::from($statusStr);
            $order = $this->orderService->updateStatus($order, $newStatus);

            $store->logActivity('order_status_updated', $request->user(), [
                'entity_type' => 'order',
                'entity_id' => $order->id,
                'new_values' => ['status' => $newStatus->value],
            ]);

            $order->load(['items.product.primaryImage', 'user', 'address', 'deliveryPartner']);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Accept order
     */
    public function accept(Request $request, int $orderId)
    {
        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->findOrFail($orderId);

        // Requirement #11: In Driver Confirmation mode, Store cannot accept unassigned orders
        $confirmBy = \App\Models\Setting::get('order_confirmation_by', 'store');
        if (in_array($confirmBy, ['driver', 'delivery_partner']) && !$order->delivery_partner_id) {
            return response()->json([
                'success' => false,
                'message' => 'This order requires driver confirmation first.',
            ], 403);
        }

        try {
            $order = $this->orderService->updateStatus($order, OrderStatus::CONFIRMED);

            return response()->json([
                'success' => true,
                'message' => 'Order accepted',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject order
     */
    public function reject(Request $request, int $orderId)
    {
        $storeCancelEnabled = \App\Models\Setting::get('store_cancellation_enabled', '1');
        if ($storeCancelEnabled !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Store cancellation is disabled by admin.',
            ], 403);
        }

        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->findOrFail($orderId);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $order = $this->orderService->cancel($order, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Order rejected',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get pending orders count
     */
    public function pendingCount(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $count = $store->orders()->where('status', OrderStatus::PENDING)->count();

        return response()->json([
            'success' => true,
            'data' => ['pending_count' => $count],
        ]);
    }

    /**
     * Get today's orders summary
     */
    public function todaySummary(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $today = now()->startOfDay();

        $summary = [
            'total' => $store->orders()->whereDate('created_at', $today)->count(),
            'pending' => $store->orders()->whereDate('created_at', $today)->where('status', 'pending')->count(),
            'confirmed' => $store->orders()->whereDate('created_at', $today)->where('status', 'confirmed')->count(),
            'delivered' => $store->orders()->whereDate('created_at', $today)->where('status', 'delivered')->count(),
            'cancelled' => $store->orders()->whereDate('created_at', $today)->where('status', 'cancelled')->count(),
            'revenue' => $store->orders()->whereDate('created_at', $today)->where('status', 'delivered')->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
    /**
     * Remove item from order
     */
    public function removeItem(Request $request, int $orderId, int $itemId)
    {
        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->findOrFail($orderId);
        
        try {
            $order = $this->orderService->removeItem($order, $itemId);
            
            $order->load(['items.product.primaryImage', 'user', 'address', 'deliveryPartner']);

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update item quantity in order (decrease)
     */
    public function updateItemQuantity(Request $request, int $orderId, int $itemId)
    {
        $store = $request->user()->getCurrentStore();
        $order = $store->orders()->findOrFail($orderId);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = $this->orderService->updateItemQuantity($order, $itemId, $request->input('quantity'));

            $order->load(['items.product.primaryImage', 'user', 'address', 'deliveryPartner']);

            return response()->json([
                'success' => true,
                'message' => 'Item quantity updated',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Requirement #9: Block manual driver assignment from Store App
     */
    public function assignDeliveryPartner(Request $request, int $orderId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Manual driver assignment by stores is disabled. Delivery partners are assigned automatically by the system.',
        ], 403);
    }

    /**
     * Get available delivery partners
     */
    public function getAvailableDrivers(Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    /**
     * Get available drivers
     */
    public function availableDrivers(Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    /**
     * Requirement #9: Block manual driver assignment from Store App
     */
    public function assignDriver(Request $request, int $orderId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Manual driver assignment by stores is disabled. Delivery partners are assigned automatically by the system.',
        ], 403);
    }
}
