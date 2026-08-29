<?php

namespace App\Http\Controllers\Api\V1\DeliveryPartner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use App\Http\Resources\DeliveryPartner\DeliveryOrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private \App\Services\RealtimeService $realtimeService,
        private \App\Services\OrderChatService $chatService
    ) {}

    /**
     * List assigned or available orders
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $user = $request->user();

        $query = Order::with(['store', 'address', 'items.product.primaryImage', 'user', 'orderChats']);

        if ($status === 'available') {
            // Exclude orders that this driver has previously declined
            $declinedOrderIds = \DB::table('driver_order_declines')
                ->where('driver_id', $user->id)
                ->pluck('order_id');

            // Exclude Store Pickup orders — drivers only handle Home Delivery orders
            $query->where(function ($q) {
                $q->whereNull('order_type')->orWhere('order_type', '!=', 'pickup');
            });

            // Show unassigned orders OR orders assigned to this driver that need pickup
            $query->whereNotIn('id', $declinedOrderIds)
                  ->where(function ($q) use ($user) {
                      $q->whereNull('delivery_partner_id')
                        ->orWhere('delivery_partner_id', $user->id);
                  })->whereIn('status', [
                      OrderStatus::PENDING,
                      OrderStatus::CONFIRMED,
                      OrderStatus::PACKED,
                      OrderStatus::PROCESSING
                  ]);
        } else {
            // Orders assigned to this driver
            $query->where('delivery_partner_id', $user->id)
                  ->where(function ($q) {
                      $q->whereNull('order_type')->orWhere('order_type', '!=', 'pickup');
                  });

            if ($status === 'completed') {
                $query->where('status', OrderStatus::DELIVERED);
            } elseif ($status === 'active') {
                $query->whereIn('status', [
                    OrderStatus::CONFIRMED,
                    OrderStatus::PACKED, 
                    OrderStatus::PICKED_UP, 
                    OrderStatus::OUT_FOR_DELIVERY
                ]);
            } elseif ($status === 'new') {
                $query->whereIn('status', [OrderStatus::PENDING, OrderStatus::CONFIRMED, OrderStatus::PACKED]);
            }
        }

        $orders = $query->latest()->paginate($request->query('per_page', 20));

        return DeliveryOrderResource::collection($orders);
    }

    /**
     * Show order details
     */
    public function show(Request $request, Order $order)
    {
        $userId = $request->user()->id;
        if ($order->delivery_partner_id && (string)$order->delivery_partner_id !== (string)$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return new DeliveryOrderResource($order->load(['store', 'address', 'items.product.primaryImage', 'user', 'orderChats']));
    }

    public function accept(Request $request, Order $order)
    {
        $user = $request->user();
        $userId = $user->id;

        if ($order->order_type === 'pickup') {
            return response()->json(['success' => false, 'message' => 'This is a Store Pickup order and does not require a delivery driver.'], 422);
        }

        // Check if driver previously declined this order
        $isDeclined = \DB::table('driver_order_declines')
            ->where('order_id', $order->id)
            ->where('driver_id', $userId)
            ->exists();

        if ($isDeclined) {
            return response()->json(['success' => false, 'message' => 'You cannot accept an order you previously declined.'], 422);
        }

        if ($order->delivery_partner_id && $order->delivery_partner_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Order already assigned'], 422);
        }

        $order->update(['delivery_partner_id' => $userId]);

        // Initialize chat so driver and customer can communicate
        $this->chatService->initializeDeliveryChat($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));

        // If in driver-confirmation mode, transition to CONFIRMED and notify store
        $confirmBy = Setting::get('order_confirmation_by', 'store');
        if (in_array($confirmBy, ['driver', 'delivery_partner'])) {
            $this->orderService->updateStatus($order->fresh(), OrderStatus::CONFIRMED);
            // orderStatusChanged() will notify the store automatically
        } else {
            // Store-confirm mode: notify the store that a driver has self-assigned
            $order->loadMissing(['store.owner']);
            $storeOwner = $order->store?->owner;
            if ($storeOwner && $storeOwner->fcm_token) {
                $this->realtimeService->getFcmService()->sendToDevice($storeOwner->fcm_token, [
                    'title' => 'Driver Assigned',
                    'body' => "A driver has accepted order #{$order->order_number}",
                ], [
                    'type' => 'order_status',
                    'order_id' => (string)$order->id,
                    'screen' => 'order_detail',
                    'id' => (string)$order->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            }
        }

        return new DeliveryOrderResource($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));
    }

    /**
     * Reject / Decline order
     */
    public function reject(Request $request, Order $order)
    {
        $userId = $request->user()->id;

        // Record driver decline in database
        \DB::table('driver_order_declines')->updateOrInsert(
            ['order_id' => $order->id, 'driver_id' => $userId],
            [
                'reason' => $request->input('reason', 'Driver declined order'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($order->delivery_partner_id === $userId) {
            $order->update(['delivery_partner_id' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order declined successfully',
        ]);
    }

    /**
     * Driver cancel order (if enabled by admin)
     */
    public function cancel(Request $request, Order $order)
    {
        $enabled = Setting::get('driver_cancellation_enabled', '0');
        if ($enabled !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Driver cancellation is disabled by admin.',
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->validateOwnership($request, $order);

        try {
            $order = $this->orderService->cancel($order, 'Driver Cancellation: ' . $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => new DeliveryOrderResource($order->load(['store', 'address', 'items', 'user', 'orderChats'])),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Mark as picked up
     */
    public function pickup(Request $request, Order $order)
    {
        $this->validateOwnership($request, $order);

        // If order is still CONFIRMED (driver accepted but store hasn't explicitly packed),
        // silently transition through PACKED without broadcasting to avoid a redundant
        // "Order Ready for Pickup" notification that the driver would immediately dismiss.
        if ($order->status === OrderStatus::CONFIRMED) {
            // Directly update status without going through updateStatus() to skip the broadcast
            $order->update([
                'status' => OrderStatus::PACKED,
                'packed_at' => now(),
            ]);
            $order->refresh();
        }

        $this->orderService->updateStatus($order, OrderStatus::PICKED_UP);

        return new DeliveryOrderResource($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));
    }

    /**
     * Mark as out for delivery (Generates customer Delivery OTP)
     */
    public function outForDelivery(Request $request, Order $order)
    {
        $this->validateOwnership($request, $order);

        // Generate 4-digit delivery OTP if not already set
        if (empty($order->delivery_otp)) {
            $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $order->delivery_otp = $otp;
            $order->save();

            // Notify customer via FCM Push Notification
            $customer = $order->user;
            if ($customer && $customer->fcm_token) {
                $this->realtimeService->getFcmService()->sendToDevice($customer->fcm_token, [
                    'title' => '🔑 Delivery OTP',
                    'body' => "Your driver is out for delivery! Give OTP {$otp} to the driver upon delivery.",
                ], [
                    'type' => 'delivery_otp',
                    'order_id' => (string)$order->id,
                    'otp' => (string)$otp,
                ]);
            }

            // Send Email notification to customer
            if ($customer && $customer->email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Hello {$customer->name},\n\nYour order #{$order->order_number} is out for delivery!\nPlease share this OTP with your delivery partner: {$otp}\n\nThank you for choosing InAllCart!",
                        function ($message) use ($customer, $order) {
                            $message->to($customer->email)
                                    ->subject("Delivery OTP for Order #{$order->order_number}");
                        }
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Delivery OTP Email failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $this->orderService->updateStatus($order, OrderStatus::OUT_FOR_DELIVERY);

        return new DeliveryOrderResource($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));
    }

    /**
     * Mark as delivered (Enforces OTP Verification)
     */
    public function deliver(Request $request, Order $order)
    {
        $this->validateOwnership($request, $order);

        // Enforce OTP check if OTP exists on order
        if (!empty($order->delivery_otp)) {
            $request->validate(['otp' => 'required|string|size:4']);
            if ((string)$request->otp !== (string)$order->delivery_otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid delivery OTP. Please ask customer for the 4-digit OTP code.',
                ], 422);
            }
            $order->otp_verified_at = now();
            $order->save();
        }

        // OrderService::updateStatus handles financial settlements
        $this->orderService->updateStatus($order, OrderStatus::DELIVERED);

        return new DeliveryOrderResource($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));
    }

    private function validateOwnership(Request $request, Order $order)
    {
        $userId = $request->user()->id;

        if (!$order->delivery_partner_id) {
            $order->update(['delivery_partner_id' => $userId]);
            $order->refresh();
        }

        if ((string)$order->delivery_partner_id !== (string)$userId) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Remove item from order
     */
    public function removeItem(Request $request, Order $order, $itemId)
    {
        $this->validateOwnership($request, $order);
        
        try {
            $order = $this->orderService->removeItem($order, $itemId);
            
            return new DeliveryOrderResource($order->fresh(['store', 'address', 'items', 'user', 'orderChats']));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
