<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use App\Enums\OrderStatus;
use App\Services\OrderChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderChatController extends Controller
{
    public function __construct(
        private OrderChatService $chatService
    ) {}

    /**
     * Initialize or get chat for order
     * POST /api/v1/orders/{orderId}/chat/init
     */
    public function initializeChat(Request $request, int $orderId)
    {
        $request->validate([
            'chat_type' => 'required|in:customer_delivery,customer_seller',
        ]);

        $order = Order::with(['user', 'deliveryPartner', 'store.owner'])
            ->findOrFail($orderId);

        // Requirement #16: Block chat for completed or cancelled orders
        if (in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::CANCELLED, 'delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chat is disabled for completed or cancelled orders.',
            ], 403);
        }

        // Verify user has access to this order
        $user = Auth::user();
        $isStoreOwner = $order->store && $order->store->owner_id === $user->id;
        if ($order->user_id !== $user->id && 
            $order->delivery_partner_id !== $user->id && 
            !$isStoreOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chatType = $request->chat_type;
        
        // Initialize appropriate chat
        $chat = $chatType === 'customer_delivery'
            ? $this->chatService->initializeDeliveryChat($order)
            : $this->chatService->initializeSellerChat($order);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => $chatType === 'customer_delivery'
                    ? 'Delivery partner not yet assigned to this order.'
                    : 'Support chat is not available for this order.',
            ], 400);
        }

        // Requirement #18: For seller chat, display Store Name instead of owner personal name
        $participantName = $chat->participant?->name;
        if ($chat->chat_type === 'customer_seller' && $order->store) {
            $participantName = $order->store->name;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'chat_id' => $chat->id,
                'firebase_chat_id' => $chat->firebase_chat_id,
                'chat_type' => $chat->chat_type,
                'order_id' => $chat->order_id,
                'customer' => [
                    'id' => $chat->customer->id,
                    'name' => $chat->customer->name,
                    'avatar' => storage_url($chat->customer->avatar),
                ],
                'participant' => $chat->participant ? [
                    'id' => $chat->participant->id,
                    'name' => $participantName,
                    'avatar' => storage_url($order->store->logo ?? $chat->participant->avatar),
                    'phone' => $order->store->phone ?? $chat->participant->phone,
                ] : null,
                'is_active' => $chat->is_active,
                'unread_count' => $user->id === $chat->customer_id 
                    ? $chat->unread_count_customer 
                    : $chat->unread_count_participant,
            ],
        ]);
    }

    /**
     * Get chat details
     * GET /api/v1/orders/{orderId}/chat/{chatType}
     */
    public function getChat(int $orderId, string $chatType)
    {
        if (!in_array($chatType, ['customer_delivery', 'customer_seller'])) {
            return response()->json(['message' => 'Invalid chat type'], 400);
        }

        $chat = $this->chatService->getChat($orderId, $chatType);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $order = Order::with('store')->find($orderId);

        // Verify access
        $user = Auth::user();
        if ($chat->customer_id !== $user->id && $chat->participant_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Requirement #18: Store name for customer_seller chat
        $participantName = $chat->participant?->name;
        if ($chat->chat_type === 'customer_seller' && $order && $order->store) {
            $participantName = $order->store->name;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'chat_id' => $chat->id,
                'firebase_chat_id' => $chat->firebase_chat_id,
                'chat_type' => $chat->chat_type,
                'order_id' => $chat->order_id,
                'customer' => [
                    'id' => $chat->customer->id,
                    'name' => $chat->customer->name,
                    'avatar' => storage_url($chat->customer->avatar),
                ],
                'participant' => $chat->participant ? [
                    'id' => $chat->participant->id,
                    'name' => $participantName,
                    'avatar' => storage_url($order?->store->logo ?? $chat->participant->avatar),
                    'phone' => $order?->store->phone ?? $chat->participant->phone,
                ] : null,
                'is_active' => $chat->is_active,
                'last_message' => $chat->last_message,
                'last_message_at' => $chat->last_message_at?->toIso8601String(),
                'unread_count' => $user->id === $chat->customer_id 
                    ? $chat->unread_count_customer 
                    : $chat->unread_count_participant,
            ],
        ]);
    }

    /**
     * Get all chats for an order
     * GET /api/v1/orders/{orderId}/chats
     */
    public function getOrderChats(int $orderId)
    {
        $order = Order::with('store')->findOrFail($orderId);
        $user = Auth::user();

        // Verify access
        if ($order->user_id !== $user->id && 
            $order->delivery_partner_id !== $user->id && 
            optional($order->store)->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chats = $this->chatService->getOrderChats($orderId);

        $formatChat = function($chat) use ($user, $order) {
            if (!$chat) return null;

            $participantName = $chat->participant?->name;
            if ($chat->chat_type === 'customer_seller' && $order->store) {
                $participantName = $order->store->name;
            }
            
            return [
                'chat_id' => $chat->id,
                'firebase_chat_id' => $chat->firebase_chat_id,
                'chat_type' => $chat->chat_type,
                'participant' => $chat->participant ? [
                    'id' => $chat->participant->id,
                    'name' => $participantName,
                    'avatar' => storage_url($order->store->logo ?? $chat->participant->avatar),
                    'phone' => $order->store->phone ?? $chat->participant->phone,
                ] : null,
                'is_active' => $chat->is_active,
                'last_message' => $chat->last_message,
                'last_message_at' => $chat->last_message_at?->toIso8601String(),
                'unread_count' => $user->id === $chat->customer_id 
                    ? $chat->unread_count_customer 
                    : $chat->unread_count_participant,
            ];
        };

        return response()->json([
            'success' => true,
            'data' => [
                'delivery_chat' => $formatChat($chats['delivery_chat']),
                'seller_chat' => $formatChat($chats['seller_chat']),
            ],
        ]);
    }

    /**
     * Mark chat as read
     * POST /api/v1/orders/{orderId}/chat/{chatType}/read
     */
    public function markAsRead(int $orderId, string $chatType)
    {
        if (!in_array($chatType, ['customer_delivery', 'customer_seller'])) {
            return response()->json(['message' => 'Invalid chat type'], 400);
        }

        $chat = $this->chatService->getChat($orderId, $chatType);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $user = Auth::user();
        $isCustomer = $chat->customer_id === $user->id;

        // Verify access
        if (!$isCustomer && $chat->participant_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->chatService->markAsRead($orderId, $chatType, $isCustomer);

        return response()->json([
            'success' => true,
            'message' => 'Chat marked as read',
        ]);
    }

    /**
     * Send chat notification
     * POST /api/v1/orders/{orderId}/chat/{chatType}/notify
     */
    public function sendNotification(Request $request, int $orderId, string $chatType)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'is_viewing' => 'boolean',
        ]);

        $order = Order::find($orderId);
        if ($order && in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::CANCELLED, 'delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Messaging is disabled for completed or cancelled orders.',
            ], 403);
        }

        if (!in_array($chatType, ['customer_delivery', 'customer_seller'])) {
            return response()->json(['message' => 'Invalid chat type'], 400);
        }

        $chat = $this->chatService->getChat($orderId, $chatType);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $user = Auth::user();
        $isCustomer = $chat->customer_id === $user->id;
        $isAdmin = $user->role === 'admin' || $user->role === 'super_admin';

        // Verify access
        if (!$isCustomer && $chat->participant_id !== $user->id && !$isAdmin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if this is first reply from participant
        $isFirstReply = false;
        if (!$isCustomer && !$isAdmin) {
            $isFirstReply = $this->chatService->isFirstReplyFromParticipant($chat);
        }

        // Update last message
        $this->chatService->updateLastMessage(
            $orderId,
            $chatType,
            $request->message,
            $isCustomer
        );

        // Determine if we should send notification
        $shouldNotify = false;
        $toCustomer = false;

        if ($isAdmin) {
            if ($chat->customer && $chat->customer->fcm_token) {
                $this->chatService->sendChatNotification(
                    $chat,
                    $user,
                    $request->message,
                    true,
                    true,
                    false
                );
            }
            if ($chat->participant && $chat->participant->fcm_token) {
                $this->chatService->sendChatNotification(
                    $chat,
                    $user,
                    $request->message,
                    false,
                    true,
                    false
                );
            }
            $notificationSent = true;
        } elseif (!$isCustomer) {
            $shouldNotify = true;
            $toCustomer = true;
        } else {
            $shouldNotify = true;
            $toCustomer = false;
        }

        $notificationSent = false;
        if ($shouldNotify && !$isAdmin) {
            $recipientIsViewing = $request->boolean('is_viewing', false);
            
            $notificationSent = $this->chatService->sendChatNotification(
                $chat,
                $user,
                $request->message,
                $toCustomer,
                $isAdmin,
                $recipientIsViewing
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification processed',
            'notification_sent' => $notificationSent,
            'is_first_reply' => $isFirstReply,
        ]);
    }

    /**
     * Get user's all active chats
     * GET /api/v1/chats
     */
    public function getUserChats(Request $request)
    {
        $user = Auth::user();
        $role = $request->query('role', 'customer');

        $chats = $this->chatService->getUserChats($user->id, $role);

        return response()->json([
            'success' => true,
            'data' => $chats,
        ]);
    }
}
