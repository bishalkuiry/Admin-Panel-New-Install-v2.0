<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\DeliveryTracking;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Services\OrderService;
use App\Services\BroadcastService;
use App\Services\OrderChatService;
use App\Services\RealtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private BroadcastService $broadcastService,
        private OrderChatService $chatService,
        private RealtimeService $realtimeService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['user', 'store', 'items']);

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('store_id')) {
            $query->where('store_id', $request->query('store_id'));
        }

        if ($request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->query('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->query('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $orders = $query->latest()->paginate(20);
        $stores = Store::active()->get();
        $statuses = OrderStatus::cases();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::whereIn('status', ['confirmed', 'packed'])->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'today_revenue' => Order::whereDate('created_at', today())->where('status', 'delivered')->sum('total'),
            'total_commission' => OrderItem::whereHas('order', fn($q) => $q->where('status', 'delivered'))->sum('commission'),
            'today_commission' => OrderItem::whereHas('order', fn($q) => $q->where('status', 'delivered')->whereDate('created_at', today()))->sum('commission'),
        ];

        return view('admin.orders.index', compact('orders', 'stores', 'statuses', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'store', 'items.product', 'address', 'deliveryPartner']);
        
        // Get available delivery partners (active only)
        $availablePartners = User::where('role', UserRole::DELIVERY_PARTNER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.orders.show', compact('order', 'availablePartners'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
        ]);

        $this->orderService->updateStatus($order, OrderStatus::from($request->status));

        return back()->with('success', 'Order status updated');
    }

    public function cancel(Request $request, Order $order)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->orderService->cancel($order, $request->reason);
            return back()->with('success', 'Order cancelled');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign delivery partner to order
     */
    public function assignDeliveryPartner(Request $request, Order $order)
    {
        $request->validate([
            'delivery_partner_id' => 'required|exists:users,id',
        ]);

        // Verify the user is actually a delivery partner
        $partner = User::findOrFail($request->delivery_partner_id);
        $partnerRole = $partner->getRawOriginal('role');
        if ($partnerRole !== UserRole::DELIVERY_PARTNER->value) {
            return back()->with('error', 'Selected user is not a delivery partner');
        }

        if (!$partner->is_active) {
            return back()->with('error', 'Selected delivery partner is not active');
        }

        // Get delivery partner's last known location from tracking history
        $lastTracking = DeliveryTracking::where('delivery_partner_id', $partner->id)
            ->latest('recorded_at')
            ->first();

        $order->update([
            'delivery_partner_id' => $request->delivery_partner_id,
        ]);

        // Initialize delivery chat automatically
        $this->chatService->initializeDeliveryChat($order);

        // If order is confirmed or packed, automatically move to packed status
        if (in_array($order->status->value, ['confirmed', 'packed'])) {
            $this->orderService->updateStatus($order, OrderStatus::PACKED);
        }

        // Notify Driver
        $this->realtimeService->sendDriverAssignmentNotification($partner, $order);

        return back()->with('success', 'Delivery partner assigned successfully');
    }

    /**
     * SSE stream for real-time order status updates.
     * Pulls cached broadcast events from the 'orders' channel and
     * forwards only those that match this order.
     */
    public function stream(Order $order)
    {
        // SSE headers — bypass session/cookie for streaming
        return response()->stream(function () use ($order) {
            ignore_user_abort(false);

            $startTime  = time();
            $maxSeconds = 25;        // Reconnect every 25 s to avoid proxy timeouts
            $pollEvery  = 1500000;   // µs → 1.5 s between polls
            $since      = now()->subSeconds(30)->toISOString();

            echo "retry: 4000\n\n";  // Tell browser: reconnect after 4 s on drop
            @ob_flush(); @flush();

            while ((time() - $startTime) < $maxSeconds) {
                if (connection_aborted()) break;

                // Fetch from the 'orders' channel cache
                $events = Cache::get('broadcast_events_orders', []);

                foreach ($events as $evt) {
                    // Only forward events newer than $since AND belonging to this order
                    if ($evt['timestamp'] <= $since) continue;
                    $data = $evt['data'] ?? [];
                    if (($data['order_id'] ?? null) != $order->id) continue;

                    $since = $evt['timestamp']; // advance cursor

                    echo "id: " . base64_encode($evt['timestamp']) . "\n";
                    echo "event: order-status\n";
                    echo "data: " . json_encode([
                        'order_id'   => $order->id,
                        'new_status' => $data['new_status'] ?? null,
                        'old_status' => $data['old_status'] ?? null,
                        'label'      => $data['new_status']
                            ? OrderStatus::from($data['new_status'])->label()
                            : '',
                    ]) . "\n\n";
                }

                // Heartbeat so the connection stays alive
                echo ": heartbeat\n\n";
                if (!@ob_flush() || !@flush()) break;

                usleep($pollEvery);
            }

            // Signal browser to reconnect immediately
            echo "event: reconnect\ndata: {}\n\n";
            @ob_flush(); @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Lightweight JSON polling endpoint (SSE fallback for older browsers / proxies).
     */
    public function statusCheck(Order $order)
    {
        return response()->json([
            'order_id'   => $order->id,
            'status'     => $order->getRawOriginal('status'),
            'label'      => $order->status->label(),
            'updated_at' => $order->updated_at->toISOString(),
        ]);
    }

    /**
     * Unassign delivery partner from order
     */
    public function unassignDeliveryPartner(Order $order)
    {
        if (!$order->delivery_partner_id) {
            return back()->with('error', 'No delivery partner assigned to this order');
        }

        // Don't allow unassigning if order is out for delivery or delivered
        if (in_array($order->status->value, ['out_for_delivery', 'delivered'])) {
            return back()->with('error', 'Cannot unassign delivery partner from orders that are out for delivery or delivered');
        }

        $order->update([
            'delivery_partner_id' => null,
        ]);

        return back()->with('success', 'Delivery partner removed from order');
    }

    /**
     * Remove item from order
     */
    public function removeItem(Request $request, Order $order, $itemId)
    {
        try {
            // Permission check (optional if handled by middleware)
            
            $this->orderService->removeItem($order, $itemId);
            
            return back()->with('success', 'Item removed and order updated');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
