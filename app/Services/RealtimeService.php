<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

use App\Enums\OrderStatus;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Cache;

/**
 * Real-time Service - Wrapper for BroadcastService and FCM
 * Provides convenient methods for common real-time events
 */
class RealtimeService
{
    public function __construct(
        private BroadcastService $broadcastService,
        private FirebaseCloudMessagingService $fcmService
    ) {}

    /**
     * Get the FCM service for direct notifications
     */
    public function getFcmService(): FirebaseCloudMessagingService
    {
        return $this->fcmService;
    }

    /**
     * Broadcast new order notification
     */
    public function newOrder(array $orderData): void
    {
        $this->broadcastService->broadcast('orders', 'new-order', [
            'order_id' => $orderData['id'],
            'order_number' => $orderData['order_number'],
            'total' => $orderData['total'],
            'customer' => $orderData['customer_name'] ?? 'Customer',
            'items_count' => $orderData['items_count'] ?? 0,
        ]);
        
        // Send FCM notification to admin
        $this->fcmService->sendNewOrderNotification([
            'order_id' => $orderData['id'],
            'order_number' => $orderData['order_number'],
            'total' => $orderData['total'],
            'customer' => $orderData['customer_name'] ?? 'Customer',
        ]);

        // Initialize order and settings
        $confirmBy = Setting::get('order_confirmation_by', 'store');
        $orderId = $orderData['order_id'] ?? $orderData['id'] ?? null;
        $order = $orderId ? Order::with(['user', 'store.owner'])->find($orderId) : null;

        // Notify Customer
        if ($order && $order->user) {
            // Send Email Confirmation
            $this->sendEmailNotification($order, 'order_placed');

            if ($order->user->fcm_token) {
            Log::info("Sending FCM placement notification to customer {$order->user->id}");
            $this->fcmService->sendToDevice($order->user->fcm_token, [
                'title' => 'Order Placed!',
                'body' => "Your order #{$orderData['order_number']} has been placed successfully.",
            ], [
                'type' => 'order_placed',
                'order_id' => (string)$order->id,
                'screen' => 'order_detail',
                'id' => (string)$order->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'params' => json_encode([
                    'orderId' => (string)$order->id,
                    'orderNumber' => $orderData['order_number'],
                ]),
            ]);
            }
        }

        if ($confirmBy === 'store') {
            Log::info("Order flow: Store Confirmation enabled");
            if ($order && $order->store && $order->store->owner) {
                $storeOwner = $order->store->owner;
                Log::info("Found store owner {$storeOwner->id} for store {$order->store->id}");
                if ($storeOwner->fcm_token) {
                    Log::info("Sending FCM to store owner token: " . substr($storeOwner->fcm_token, 0, 10) . "...");
                    $formattedTotal = \App\Helpers\CurrencyHelper::format((float)($orderData['total'] ?? 0));
                    $this->fcmService->sendToDevice($storeOwner->fcm_token, [
                        'title' => 'New Order Received',
                        'body' => "You have a new order #{$orderData['order_number']} for {$formattedTotal}",
                        'sound' => 'new_order',
                        'channel_id' => 'store_loud_orders_v10',
                    ], [
                        'type' => 'new_order',
                        'order_id' => (string)$order->id,
                        'order_number' => $orderData['order_number'],
                        'total_amount' => (string)($orderData['total'] ?? 0),
                        'customer_name' => $order->user->name ?? 'Customer',
                        'customer_address' => $order->address->full_address ?? ($order->address->address ?? ''),
                        'store_name' => $order->store->name ?? 'Store',
                        'store_address' => $order->store->address ?? '',
                        'currency_symbol' => CurrencyHelper::getSymbol(),
                        'screen' => 'order_detail',
                        'id' => (string)$order->id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]);
                } else {
                    Log::warning("Store owner {$storeOwner->id} has NO FCM token!");
                }
            } else {
                Log::error("Store or owner NOT FOUND for order " . ($order->id ?? 'unknown'));
            }
        } elseif ($order->order_type === 'pickup') {
            Log::info("Order flow: Store Pickup order #{$order->id}. Driver broadcast skipped.");
        } else {
            Log::info("Order flow: Driver Broadcast enabled. Sending to topic 'drivers'");
            // Notify Drivers (Broadcast to all active drivers)
            $this->fcmService->sendToTopic('drivers', [
                'title' => 'New Delivery Request',
                'body' => "New order #{$orderData['order_number']} is available for delivery",
                'sound' => 'new_order',
                'channel_id' => 'inallcart_order_alert_sound_v10',
            ], [
                'type' => 'new_delivery_request',
                'order_id' => (string)$order->id,
                'order_number' => $orderData['order_number'],
                'total_amount' => (string)($orderData['total'] ?? 0),
                'customer_name' => $order->user->name ?? 'Customer',
                'customer_address' => $order->address->full_address ?? ($order->address->address ?? ''),
                'store_name' => $order->store->name ?? 'Store',
                'store_address' => $order->store->address ?? '',
                'currency_symbol' => CurrencyHelper::getSymbol(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);
        }
    }

    /**
     * Broadcast order status change — uses admin-configured notification templates
     */
    public function orderStatusChanged(array $orderData): void
    {
        // Broadcast to general orders channel (for admin dashboard)
        $this->broadcastService->broadcast('orders', 'order-status', [
            'order_id' => $orderData['order_id'],
            'order_number' => $orderData['order_number'],
            'old_status' => $orderData['old_status'],
            'new_status' => $orderData['new_status'],
            'user_id' => $orderData['user_id'] ?? null,
        ]);

        // Load order with relationships for template replacement
        $order = Order::with(['store', 'user', 'deliveryPartner', 'items', 'address'])->find($orderData['order_id']);
        if (!$order) return;

        // SEND EMAIL NOTIFICATIONS BASED ON STATUS
        if ($orderData['new_status'] === OrderStatus::DELIVERED->value) {
            $this->sendEmailNotification($order, 'order_delivered');
        } elseif ($orderData['new_status'] === OrderStatus::OUT_FOR_DELIVERY->value) {
            $this->sendEmailNotification($order, 'order_shipped');
        } elseif ($orderData['new_status'] === OrderStatus::CONFIRMED->value) {
             // Optional: Order Confirmed email
             // $this->sendEmailNotification($order, 'order_confirmed');
        }

        // Load admin notification templates
        $templates = Cache::remember('notification_templates', 3600, function () {
            $setting = Setting::where('key', 'notification_templates')->first();
            return $setting ? json_decode($setting->value, true) : [];
        });

        // Build keyword replacement map
        $replacements = [
            '{order_id}' => (string)$order->id,
            '{order_number}' => $order->order_number ?? '',
            '{customer_name}' => $order->user->name ?? 'Customer',
            '{store_name}' => $order->store->name ?? 'Store',
            '{store_address}' => $order->store->address ?? '',
            '{delivery_address}' => $order->address->full_address ?? ($order->address->address ?? ''),
            '{total}' => CurrencyHelper::format($order->total),
            '{items_count}' => (string)$order->items->count(),
            '{status}' => OrderStatus::from($orderData['new_status'])->label(),
            '{delivery_partner_name}' => $order->deliveryPartner->name ?? '',
        ];

        $status = $orderData['new_status'];

        // Default fallback labels
        $defaultLabels = [
            'pending' => 'New Order',
            'confirmed' => 'Order Confirmed',
            'packed' => 'Order Packed',
            'picked_up' => 'Order Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
        ];

        // --- 1) Notify Customer ---
        if (!empty($orderData['user_id'])) {
            $this->broadcastService->broadcast("user.{$orderData['user_id']}", 'order-status', $orderData);

            $user = $order->user;
            if ($user && $user->fcm_token) {
                Log::info("Sending FCM notification to user {$user->id} (Order #{$order->order_number})");
                
                $title = $this->resolveTemplate($templates, $status, 'customer', 'title', $replacements, $defaultLabels[$status] ?? 'Order Updated');
                $body = $this->resolveTemplate($templates, $status, 'customer', 'body', $replacements, "Order #{$order->order_number} is now " . ($defaultLabels[$status] ?? $status));

                $this->fcmService->sendToDevice($user->fcm_token, [
                    'title' => $title,
                    'body' => $body,
                ], [
                    'type' => 'order_status',
                    'order_id' => (string)$order->id,
                    'screen' => 'order_detail',
                    'id' => (string)$order->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'params' => json_encode([
                        'orderId' => (string)$order->id,
                        'orderNumber' => $order->order_number,
                    ]),
                ]);
            } else {
                Log::warning("Cannot send FCM to user " . ($user ? $user->id : 'Unknown') . ": " . ($user ? "No FCM Token" : "User not found"));
            }
        }

        $confirmBy = \App\Models\Setting::get('order_confirmation_by', 'store');

        // --- 2) Notify Store Owner ---
        if ($order->store && $order->store->owner) {
            $storeOwner = $order->store->owner;
            if ($storeOwner->fcm_token) {
                $shouldNotifyStore = false;
                
                // Rules for Store notifications:
                if ($status === \App\Enums\OrderStatus::CONFIRMED->value && in_array($confirmBy, ['driver', 'delivery_partner'])) {
                    $shouldNotifyStore = true; // Store gets notified when driver accepts in "driver" mode
                } elseif ($status === \App\Enums\OrderStatus::CANCELLED->value) {
                    $shouldNotifyStore = true;
                }

                if ($shouldNotifyStore) {
                    $title = $this->resolveTemplate($templates, $status, 'store', 'title', $replacements, $defaultLabels[$status] ?? 'Order Updated');
                    $body = $this->resolveTemplate($templates, $status, 'store', 'body', $replacements, "Order #{$order->order_number} is now {$status}");

                    $this->fcmService->sendToDevice($storeOwner->fcm_token, [
                        'title' => $title,
                        'body' => $body,
                    ], [
                        'type' => 'order_status',
                        'order_id' => (string)$order->id,
                        'screen' => 'order_detail',
                        'id' => (string)$order->id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]);
                }
            }
        }

        // --- 3) Notify Delivery Partner ---
        if ($order->delivery_partner_id) {
            $deliveryPartner = $order->deliveryPartner;
            if ($deliveryPartner && $deliveryPartner->fcm_token) {
                $shouldNotifyDriver = false;

                // Rules for Driver notifications:
                if ($status === \App\Enums\OrderStatus::PACKED->value) {
                    $shouldNotifyDriver = true; // "Order Ready to Pickup"
                } elseif ($status === \App\Enums\OrderStatus::CANCELLED->value) {
                    $shouldNotifyDriver = true;
                }

                if ($shouldNotifyDriver) {
                    // Use "Ready for Pickup" specific default for PACKED status
                    $fallbackTitle = ($status === 'packed') ? 'Order Ready for Pickup' : ($defaultLabels[$status] ?? 'Order Updated');
                    $fallbackBody = ($status === 'packed') ? "Order #{$order->order_number} is ready for pickup" : "Order #{$order->order_number} is now {$status}";

                    $title = $this->resolveTemplate($templates, $status, 'delivery', 'title', $replacements, $fallbackTitle);
                    $body = $this->resolveTemplate($templates, $status, 'delivery', 'body', $replacements, $fallbackBody);

                    $this->fcmService->sendToDevice($deliveryPartner->fcm_token, [
                        'title' => $title,
                        'body' => $body,
                        'sound' => ($status === 'packed') ? 'order_ready_alert' : 'default',
                    ], [
                        'type' => 'order_status',
                        'order_id' => (string)$order->id,
                        'screen' => 'order_detail',
                        'id' => (string)$order->id,
                    ]);
                }
            }
        } else {
            // No driver assigned yet — in store-confirm mode, broadcast to all available
            // drivers when the order becomes available for pickup (CONFIRMED or PACKED)
            $confirmBy = \App\Models\Setting::get('order_confirmation_by', 'store');
            if (
                $confirmBy === 'store' &&
                in_array($status, [
                    \App\Enums\OrderStatus::CONFIRMED->value,
                    \App\Enums\OrderStatus::PACKED->value,
                ])
            ) {
                $broadcastTitle = $status === 'packed'
                    ? 'Order Ready for Pickup'
                    : 'New Order Available';
                $broadcastBody = $status === 'packed'
                    ? "Order #{$order->order_number} is packed and ready — grab it now!"
                    : "Order #{$order->order_number} is confirmed and available for delivery";

                Log::info("Broadcasting available order #{$order->order_number} (status: {$status}) to drivers topic");

                $this->fcmService->sendToTopic('drivers', [
                    'title' => $broadcastTitle,
                    'body' => $broadcastBody,
                    'sound' => 'new_order',
                    'channel_id' => 'inallcart_order_alert_sound_v10',
                ], [
                    'type' => 'new_delivery_request',
                    'order_id' => (string)$order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (string)($order->total ?? 0),
                    'customer_name' => $order->user->name ?? 'Customer',
                    'customer_address' => $order->address->full_address ?? ($order->address->address ?? ''),
                    'store_name' => $order->store->name ?? 'Store',
                    'store_address' => $order->store->address ?? '',
                    'currency_symbol' => CurrencyHelper::getSymbol(),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            }
        }
    }

    /**
     * Resolve a notification template — replace keywords or return default
     */
    private function resolveTemplate(array $templates, string $status, string $recipient, string $field, array $replacements, string $default): string
    {
        $text = $templates[$status][$recipient][$field] ?? '';
        if (empty(trim($text))) {
            return $default;
        }
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Broadcast order cancellation
     */
    public function orderCancelled(array $orderData): void
    {
        $this->broadcastService->broadcast('orders', 'order-cancelled', [
            'order_id' => $orderData['id'],
            'order_number' => $orderData['order_number'],
            'reason' => $orderData['reason'] ?? null,
        ]);
    }

    /**
     * Broadcast low stock alert
     */
    public function lowStock(array $productData): void
    {
        $this->broadcastService->broadcast('inventory', 'low-stock', [
            'product_id' => $productData['id'],
            'product_name' => $productData['name'],
            'sku' => $productData['sku'] ?? null,
            'quantity' => $productData['quantity'],
            'threshold' => $productData['threshold'],
        ]);
        
        // Send FCM notification to admin
        $this->fcmService->sendLowStockAlert([
            'product_id' => $productData['id'],
            'product_name' => $productData['name'],
            'quantity' => $productData['quantity'],
        ]);
    }

    /**
     * Broadcast out of stock alert
     */
    public function outOfStock(array $productData): void
    {
        $this->broadcastService->broadcast('inventory', 'out-of-stock', [
            'product_id' => $productData['id'],
            'product_name' => $productData['name'],
            'sku' => $productData['sku'] ?? null,
        ]);
    }

    /**
     * Broadcast product update
     */
    public function productUpdated(array $productData): void
    {
        $this->broadcastService->broadcast('inventory', 'product-update', [
            'product_id' => $productData['id'],
            'product_name' => $productData['name'],
            'action' => $productData['action'] ?? 'updated',
        ]);
    }

    /**
     * Broadcast admin notification
     */
    public function adminNotification(string $title, string $message, string $type = 'info'): void
    {
        $this->broadcastService->broadcast('admin', 'notification', [
            'title' => $title,
            'message' => $message,
            'type' => $type, // info, success, warning, error
        ]);
    }

    /**
     * Broadcast to specific user
     */
    public function notifyUser(int $userId, string $event, array $data): void
    {
        $this->broadcastService->broadcast("user.{$userId}", $event, $data);
    }

    /**
     * Get driver info
     */
    public function getDriverInfo(): array
    {
        return $this->broadcastService->getDriverInfo();
    }

    /**
     * Stream SSE events
     */
    public function stream(array $channels, ?string $lastEventId = null): void
    {
        $this->broadcastService->streamSSE($channels, $lastEventId);
    }

    /**
     * Send driver assignment notification
     */
    public function sendDriverAssignmentNotification(\App\Models\User $driver, \App\Models\Order $order): void
    {
        $storeName = $order->store->name ?? 'Store';
        $pickupAddress = $order->store->address ?? 'Store Location';
        $deliveryAddress = $order->address->full_address ?? ($order->address->address_line ?? 'Customer Location');
        $itemsCount = $order->items->count();
        $total = $order->total;

        // Broadcast to driver channel
        $this->broadcastService->broadcast("driver.{$driver->id}", 'new-assignment', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'store_name' => $storeName,
            'pickup_address' => $pickupAddress,
            'delivery_address' => $deliveryAddress,
            'items_count' => $itemsCount,
            'total' => $total,
        ]);
        
        // Send FCM notification with custom sound
        if ($driver->fcm_token) {
            Log::info("Sending assignment FCM to driver {$driver->id}");
            $this->fcmService->sendToDevice($driver->fcm_token, [
                'title' => 'New Delivery Assigned',
                'body' => "Order #{$order->order_number} from {$storeName} • {$itemsCount} items",
                'sound' => 'new_order',
                'channel_id' => 'quixko_new_deliveries_v2',
            ], [
                'type' => 'new_assignment',
                'order_id' => (string)$order->id,
                'order_number' => $order->order_number,
                'store_name' => $storeName,
                'pickup_address' => $pickupAddress,
                'delivery_address' => $deliveryAddress,
                'items_count' => (string)$itemsCount,
                'total' => (string)$total,
            ]);
        } else {
            Log::warning("Driver {$driver->id} has no FCM token for assignment notification");
        }
    }

    /**
     * Send email notification using the template system
     */
    private function sendEmailNotification(\App\Models\Order $order, string $templateType): void
    {
        try {
            // Dispatch Job
            \App\Jobs\SendTransactionalEmailJob::dispatch(
                $order,
                $templateType
            );

            Log::info("Dispatched email job [{$templateType}] to {$order->user->email}");

        } catch (\Exception $e) {
            Log::error("Failed to dispatch email job [{$templateType}]: " . $e->getMessage());
        }
    }

    /**
     * Get events for polling
     */
    public function getEvents(string $channel, ?string $since = null): array
    {
        return $this->broadcastService->getEvents($channel, $since);
    }
}
