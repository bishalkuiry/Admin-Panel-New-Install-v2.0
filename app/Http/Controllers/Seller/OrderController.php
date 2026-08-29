<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $store = $request->current_store;
        $query = Order::where('store_id', $store->id)->with(['user', 'items']);

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(15);
        $statuses = OrderStatus::cases();

        return view('seller.orders.index', compact('orders', 'statuses', 'store'));
    }

    public function show(Request $request, Order $order)
    {
        $store = $request->current_store;
        
        // Ensure order belongs to store
        if ($order->store_id !== $store->id) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['user', 'items.product', 'address', 'deliveryPartner']);
        
        return view('seller.orders.show', compact('order', 'store'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $store = $request->current_store;
        
        // Ensure order belongs to store
        if ($order->store_id !== $store->id) {
            abort(403, 'Unauthorized access to order');
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
        ]);

        $newStatus = OrderStatus::from($request->status);

        // Optional: Add logic to restrict what statuses a seller can move to
        // For example, sellers can move from pending to confirmed/packed
        
        try {
            $this->orderService->updateStatus($order, $newStatus);
            return back()->with('success', "Order status updated to {$newStatus->label()}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
