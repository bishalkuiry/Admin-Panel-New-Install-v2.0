<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderChat;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Order Chat Service
 * Manages chat sessions between customers, delivery partners, and sellers
 */
class OrderChatService
{
    public function __construct(
        private FirebaseCloudMessagingService $fcmService
    ) {}

    /**
     * Initialize chat for customer and delivery partner
     */
    public function initializeDeliveryChat(Order $order): ?OrderChat
    {
        if (!$order->delivery_partner_id) {
            return null;
        }

        return OrderChat::getOrCreateChat(
            orderId: $order->id,
            chatType: 'customer_delivery',
            customerId: $order->user_id,
            participantId: $order->delivery_partner_id
        );
    }

    /**
     * Initialize chat for customer and seller
     */
    public function initializeSellerChat(Order $order): ?OrderChat
    {
        if (!$order->store_id) {
            return null;
        }

        $store = $order->store;
        if (!$store) {
            return null;
        }

        // Use store owner if available, otherwise fall back to an admin user
        // (covers single-vendor / admin-managed stores with no owner_id)
        $participantId = $store->owner_id
            ?? User::whereIn('role', [\App\Enums\UserRole::SUPER_ADMIN->value, \App\Enums\UserRole::ADMIN->value])
                ->value('id');

        if (!$participantId) {
            return null;
        }

        return OrderChat::getOrCreateChat(
            orderId: $order->id,
            chatType: 'customer_seller',
            customerId: $order->user_id,
            participantId: $participantId
        );
    }

    /**
     * Get chat for order
     */
    public function getChat(int $orderId, string $chatType): ?OrderChat
    {
        return OrderChat::where('order_id', $orderId)
            ->where('chat_type', $chatType)
            ->with(['customer', 'participant', 'order'])
            ->first();
    }

    /**
     * Get all chats for an order
     */
    public function getOrderChats(int $orderId): array
    {
        $chats = OrderChat::where('order_id', $orderId)
            ->with(['customer', 'participant', 'order'])
            ->get();

        return [
            'delivery_chat' => $chats->firstWhere('chat_type', 'customer_delivery'),
            'seller_chat' => $chats->firstWhere('chat_type', 'customer_seller'),
        ];
    }

    /**
     * Send chat notification with smart logic
     */
    public function sendChatNotification(
        OrderChat $chat,
        User $sender,
        string $message,
        bool $toCustomer = false,
        bool $isAdminSender = false,
        bool $recipientIsViewing = false
    ): bool {
        // Don't send notification if recipient is currently viewing the chat
        if ($recipientIsViewing) {
            return false;
        }

        $recipient = $toCustomer ? $chat->customer : $chat->participant;

        if (!$recipient || !$recipient->fcm_token) {
            return false;
        }

        $orderNumber = $chat->order->order_number ?? $chat->order_id;

        $chatTypeLabel = $chat->chat_type === 'customer_delivery' 
            ? 'Delivery Partner' 
            : 'Store';

        // Determine notification title based on sender (Requirement #15: Include Order ID)
        if ($isAdminSender) {
            $title = "Admin message for Order #{$orderNumber}";
        } elseif ($toCustomer) {
            $title = "{$chatTypeLabel} message for Order #{$orderNumber}";
        } else {
            $title = "New message for Order #{$orderNumber}";
        }

        $result = $this->fcmService->sendToUser($recipient, [
            'title' => $title,
            'body' => substr($message, 0, 100),
        ], [
            'type' => 'order_chat',
            'order_id' => (string) $chat->order_id,
            'chat_id' => $chat->firebase_chat_id,
            'chat_type' => $chat->chat_type,
            'sender_id' => (string) $sender->id,
            'sender_name' => $sender->name,
            'sender_role' => $isAdminSender ? 'admin' : ($toCustomer ? 'participant' : 'customer'),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'deep_link' => "quixko://order/{$chat->order_id}/chat/{$chat->chat_type}",
            'screen' => 'order_chat',
        ]);

        return $result;
    }

    /**
     * Check if this is the first reply from participant to customer
     */
    public function isFirstReplyFromParticipant(OrderChat $chat): bool
    {
        // If there's no last message, this is the first message
        return empty($chat->last_message);
    }

    /**
     * Update last message info
     */
    public function updateLastMessage(
        int $orderId,
        string $chatType,
        string $message,
        bool $fromCustomer = true
    ): void {
        $chat = $this->getChat($orderId, $chatType);

        if ($chat) {
            $chat->update([
                'last_message' => $message,
                'last_message_at' => now(),
            ]);

            // Increment unread count for recipient
            $chat->incrementUnread(!$fromCustomer);
        }
    }

    /**
     * Mark chat as read
     */
    public function markAsRead(int $orderId, string $chatType, bool $isCustomer = true): void
    {
        $chat = $this->getChat($orderId, $chatType);
        $chat?->markAsRead($isCustomer);
    }

    /**
     * Get user's active chats
     */
    public function getUserChats(int $userId, string $role = 'customer'): array
    {
        $query = OrderChat::with(['order', 'customer', 'participant'])
            ->where('is_active', true)
            ->orderBy('last_message_at', 'desc');

        if ($role === 'customer') {
            $query->where('customer_id', $userId);
        } else {
            $query->where('participant_id', $userId);
        }

        return $query->get()->toArray();
    }

    /**
     * Close chat when order is completed/cancelled
     */
    public function closeOrderChats(int $orderId): void
    {
        OrderChat::where('order_id', $orderId)
            ->update(['is_active' => false]);
    }
}
