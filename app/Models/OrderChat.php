<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderChat extends Model
{
    protected $fillable = [
        'order_id',
        'firebase_chat_id',
        'chat_type',
        'customer_id',
        'participant_id',
        'admin_id',
        'admin_joined_at',
        'last_message_at',
        'last_message',
        'unread_count_customer',
        'unread_count_participant',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'admin_joined_at' => 'datetime',
        'unread_count_customer' => 'integer',
        'unread_count_participant' => 'integer',
        'is_active' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Generate Firebase chat ID
     */
    public static function generateFirebaseChatId(int $orderId, string $chatType): string
    {
        return "{$orderId}_{$chatType}_" . time();
    }

    /**
     * Get or create chat for order
     */
    public static function getOrCreateChat(int $orderId, string $chatType, int $customerId, ?int $participantId = null): self
    {
        $chat = self::where('order_id', $orderId)
            ->where('chat_type', $chatType)
            ->first();

        if (!$chat) {
            $chat = self::create([
                'order_id' => $orderId,
                'firebase_chat_id' => self::generateFirebaseChatId($orderId, $chatType),
                'chat_type' => $chatType,
                'customer_id' => $customerId,
                'participant_id' => $participantId,
                'is_active' => true,
            ]);
        }

        return $chat;
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(bool $isCustomer = true): void
    {
        if ($isCustomer) {
            $this->update(['unread_count_customer' => 0]);
        } else {
            $this->update(['unread_count_participant' => 0]);
        }
    }

    /**
     * Increment unread count
     */
    public function incrementUnread(bool $forCustomer = true): void
    {
        if ($forCustomer) {
            $this->increment('unread_count_customer');
        } else {
            $this->increment('unread_count_participant');
        }
    }
}
