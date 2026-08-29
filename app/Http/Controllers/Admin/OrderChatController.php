<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\User;
use App\Services\OrderChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderChatController extends Controller
{
    public function __construct(
        private OrderChatService $chatService
    ) {}

    /**
     * Show chat interface for admin
     */
    public function show(Order $order, string $chatType)
    {
        if (!in_array($chatType, ['customer_delivery', 'customer_seller'])) {
            return back()->with('error', 'Invalid chat type');
        }

        $chat = $this->chatService->getChat($order->id, $chatType);

        if (!$chat) {
            return back()->with('error', 'Chat not found. Please ensure the order has a delivery partner or seller assigned.');
        }

        // Load relationships
        $chat->load(['customer', 'participant', 'admin']);

        // Add admin to chat if not already
        if (!$chat->admin_id) {
            $chat->update(['admin_id' => Auth::id()]);
        }

        $order->load(['user', 'store', 'deliveryPartner']);

        return view('admin.orders.chat', compact('order', 'chat', 'chatType'));
    }

    /**
     * Send notification from admin panel (web route)
     */
    public function sendNotification(Request $request, Order $order, string $chatType)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'admin_id' => 'required|integer|exists:users,id',
        ]);

        $admin = User::find($request->admin_id);
        
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        if (!in_array($chatType, ['customer_delivery', 'customer_seller'])) {
            return response()->json(['message' => 'Invalid chat type'], 400);
        }

        $chat = $this->chatService->getChat($order->id, $chatType);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        // Update last message
        $this->chatService->updateLastMessage(
            $order->id,
            $chatType,
            $request->message,
            false
        );

        // Send to customer
        $customerNotified = false;
        if ($chat->customer && $chat->customer->fcm_token) {
            $customerNotified = $this->chatService->sendChatNotification(
                $chat,
                $admin,
                $request->message,
                true,
                true,
                false
            );
        }
        
        // Send to participant
        $participantNotified = false;
        if ($chat->participant && $chat->participant->fcm_token) {
            $participantNotified = $this->chatService->sendChatNotification(
                $chat,
                $admin,
                $request->message,
                false,
                true,
                false
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification processed',
            'customer_notified' => $customerNotified,
            'participant_notified' => $participantNotified,
        ]);
    }
}

