<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Address;
use App\Models\Zone;
use App\Models\ProductVariant;
use App\Models\CouponUsage;
use App\Models\DeliveryCalculationLog;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Events\LowStockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\CartService;

class OrderService
{
    public function __construct(
        private RealtimeService $realtimeService,
        private WalletService $walletService,
        private DeliveryCalculationService $deliveryCalculationService,
        private PointsService $pointsService,
        private ReferralService $referralService,
        private \App\Services\TaxService $taxService,
        private CartService $cartService
    ) {}

    public function create(User $user, array $data): Order|array
    {
        // Check if multi-vendor cart is enabled
        $singleStoreCart = Setting::get('single_store_cart', '1');
        
        if ($singleStoreCart == '0') {
            // Multi-vendor mode: group by store. Returns a single Order when
            // all items are from one store, or an array of Orders otherwise.
            return $this->createMultiVendorOrders($user, $data);
        }
        
        // Single-vendor mode: create one order
        return $this->createSingleOrder($user, $data);
    }

    /**
     * Create single order (single-vendor mode)
     */
    private function createSingleOrder(User $user, array $data, bool $shouldClearCart = true): Order
    {
        return DB::transaction(function () use ($user, $data, $shouldClearCart) {
            // Generate temporary order number (will be updated with ID)
            $orderNumber = 'TEMP-' . Str::random(10);
            
            // Calculate totals
            $subtotal = 0;
            $items = [];
            $storeId = null;
            $needsProductPurge = false;
            
            foreach ($data['items'] as $item) {
                Log::info('OrderService: Processing item', ['product_id' => $item['product_id']]);

                // Pessimistic lock — prevents concurrent orders from both
                // reading the same stock level and over-selling.
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                
                $variant = null;
                $price = $product->price;
                $mrp = $product->compare_price ?? $product->price;
                $sku = $product->sku;
                $productName = $product->name;
                $variantName = null;

                if (!empty($item['variant_id'])) {
                    // Lock the variant row too so concurrent orders on the
                    // same variant cannot both pass the stock check.
                    $variant = ProductVariant::lockForUpdate()
                        ->where('product_id', $product->id)
                        ->findOrFail($item['variant_id']);
                    $price = $variant->selling_price ?? $variant->mrp ?? $price;
                    $mrp = $variant->mrp ?? $mrp;
                    $sku = $variant->sku ?? $sku; // if sku is available
                    $variantName = $variant->display_name;
                    $taxRate = $variant->tax_rate ?? $product->tax_rate;
                    
                    // Check stock
                    if ($variant->quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name} - {$variantName}");
                    }
                } else {
                    $taxRate = $product->tax_rate;
                    // Check stock
                    if ($product->quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                }
                
                // Track store_id for delivery calculation
                if ($product->store_id) {
                    $storeId = $product->store_id;
                }
                
                $itemTotal = $price * $item['quantity'];
                $subtotal += $itemTotal;
                
                // Calculate and persist commission at the time of order
                $itemCommission = 0;
                if ($product->store_id) {
                    $itemCommission = $this->taxService->calculateCommission($product->store, $itemTotal, $product);
                }

                $items[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name' => $productName,
                    'product_sku' => $sku,
                    'variant_name' => $variantName,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'mrp' => $mrp,
                    'tax_rate' => $taxRate,
                    'commission' => $itemCommission,
                    'total' => $itemTotal,
                    'options' => $item['options'] ?? null,
                    'prescription_image' => $item['prescription_image'] ?? $item['prescription'] ?? null,
                ];
                
                // Reduce stock
                if ($variant) {
                    $variant->decrement('quantity', $item['quantity']);
                }
                $product->decrement('quantity', $item['quantity']);
                
                // Check low stock - If it hits low stock, we mark for cache purge
                if ($product->quantity <= $product->low_stock_threshold) {
                    $needsProductPurge = true;
                    $this->realtimeService->lowStock([
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => $product->quantity,
                        'threshold' => $product->low_stock_threshold,
                    ]);
                }
            }

            // Apply discount if coupon
            $discount = 0;
            $appliedCoupon = null;
            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();
                if ($coupon && $coupon->canBeUsedBy($user)) {
                    $discount = $this->calculateDiscount($data['coupon_code'], $subtotal, $user);
                    $appliedCoupon = $coupon;
                }
            }
            
            // Get user's cart ID for delivery calculation logging
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                Log::info('OrderService: DB Cart State at Order start', [
                    'user_id' => $user->id,
                    'is_cart_empty' => $cart->items()->count() === 0,
                    'request_items_count' => count($data['items'])
                ]);
            }
            $cartId = $cart ? $cart->id : null;
            
            // Calculate shipping using new delivery calculation service
            $deliveryResult = $this->deliveryCalculationService->calculate([
                'store_id' => $storeId,
                'address_id' => $data['address_id'] ?? null,
                'subtotal' => $subtotal,
                'distance' => $data['distance'] ?? null,
                'user_id' => $user->id,
                'cart_id' => $cartId,
                'order_id' => null,
                'coupon_code' => $data['coupon_code'] ?? null,
            ]);
            
            $orderType = strtolower($data['order_type'] ?? $data['delivery_type'] ?? 'delivery');
            if ($orderType === 'pickup') {
                $shipping = 0.00;
            } else {
                $shipping = $deliveryResult['final_charge'];
            }

            // Check Active Customer VIP Membership Perks
            $userMembership = \App\Models\UserMembership::with('plan')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($userMembership && $userMembership->plan) {
                $plan = $userMembership->plan;
                
                // 1. VIP Member Extra Discount
                if ($plan->extra_discount_percentage > 0) {
                    $vipDiscount = round($subtotal * ($plan->extra_discount_percentage / 100), 2);
                    if ($plan->max_discount_per_order > 0) {
                        $vipDiscount = min($vipDiscount, (float) $plan->max_discount_per_order);
                    }
                    $discount += $vipDiscount;
                    $userMembership->increment('total_discount_saved', $vipDiscount);
                }
                
                // 2. VIP Free Delivery Perk
                if ($orderType !== 'pickup' && $plan->free_delivery) {
                    $minOrderMet = $subtotal >= ($plan->min_order_for_free_delivery ?? 0);
                    $usageLimitNotReached = $userMembership->free_deliveries_used_this_month < ($plan->max_free_deliveries_per_month ?? 10);
                    $distanceMet = true;
                    if (isset($data['distance']) && $plan->max_delivery_distance_km > 0) {
                        $distanceMet = ((float) $data['distance']) <= (float) $plan->max_delivery_distance_km;
                    }

                    if ($minOrderMet && $usageLimitNotReached && $distanceMet) {
                        $shipping = 0.00;
                        $userMembership->increment('free_deliveries_used_this_month');
                    }
                }
            }
            
            // Calculate tax dynamically using TaxService
            $tax = $this->taxService->calculateForOrder($items, $subtotal, $discount, $storeId);
            
            // Driver tip (100% remitted to rider)
            $driverTip = (float) ($data['driver_tip'] ?? $data['tip'] ?? $data['delivery_man_tips'] ?? 0);

            // Total
            $total = $subtotal - $discount + $shipping + $tax + $driverTip;
            
            // Process wallet payment if requested
            $walletAmount = 0.00;
            $remainingAmount = $total;
            $paymentStatus = 'pending';

            $useWallet = $data['use_wallet'] ?? false;

            if (isset($data['_precomputed_wallet'])) {
                // Multi-vendor path: wallet was already debited once at the top level.
                // Just record the proportional share on this order — no debit here.
                $walletAmount    = (float) $data['_precomputed_wallet'];
                $remainingAmount = (float) $data['_precomputed_remaining'];
                if ($remainingAmount <= 0) {
                    $paymentStatus = 'paid';
                } elseif ($walletAmount > 0) {
                    $paymentStatus = 'partial';
                }
            } elseif ($useWallet) {
                $tempOrder = new Order([
                    'order_number' => $orderNumber,
                    'total' => $total,
                ]);

                $paymentResult = $this->walletService->processOrderPayment(
                    $user,
                    $tempOrder,
                    $total,
                    $useWallet,
                    $data['payment_method']
                );

                $walletAmount    = $paymentResult['wallet_amount'];
                $remainingAmount = $paymentResult['remaining_amount'];

                if ($remainingAmount == 0) {
                    $paymentStatus = 'paid';
                } elseif ($walletAmount > 0) {
                    $paymentStatus = 'partial';
                }
            }
            
            // Total Admin Commission
            $totalAdminCommission = array_sum(array_column($items, 'commission'));
            
            // Coupon Funding Ownership
            $couponFundedBy = $appliedCoupon ? ($appliedCoupon->funded_by ?? 'admin') : 'admin';
            $sellerCouponDeduction = ($couponFundedBy === 'seller') ? $discount : (($couponFundedBy === 'shared') ? ($discount / 2) : 0);
            
            // Store Earning (Product Revenue minus Commission minus Store Coupon Share)
            $storeEarning = max(0, $subtotal - $totalAdminCommission - $sellerCouponDeduction);
            
            // Driver Earning (Delivery Fee + Customer Tip; 0 if pickup)
            $driverEarning = ($orderType === 'pickup') ? 0.00 : ($shipping + $driverTip);

            // Create order
            $order = Order::create([
                'user_id'          => $user->id,
                'store_id'         => $storeId,
                'order_number'     => $orderNumber,
                'idempotency_key'  => $data['idempotency_key'] ?? null,
                'status'           => OrderStatus::PENDING,
                'order_type'       => $orderType,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'delivery_fee'     => $shipping,
                'driver_tip'       => $driverTip,
                'tax'              => $tax,
                'total'            => $total,
                'store_earning'    => $storeEarning,
                'admin_commission' => $totalAdminCommission,
                'driver_earning'   => $driverEarning,
                'coupon_funded_by' => $couponFundedBy,
                'wallet_amount'    => $walletAmount,
                'address_id'       => $data['address_id'],
                'payment_method'   => $data['payment_method'],
                'payment_status'   => $paymentStatus,
                'notes'            => $data['notes'] ?? null,
                'delivery_otp'     => str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                'prescription'     => $data['prescription'] ?? $data['prescription_image'] ?? null,
                'prescription_image' => $data['prescription'] ?? $data['prescription_image'] ?? null,
            ]);

            // Create Double-Entry Order Financial Ledger Entries
            \App\Models\OrderFinancialLedger::create([
                'order_id' => $order->id,
                'type' => 'ORDER_PAYMENT',
                'user_id' => $user->id,
                'amount' => $total,
                'entry_type' => 'credit',
                'description' => "Customer payment for order #{$order->order_number}",
                'metadata' => ['payment_method' => $data['payment_method'], 'wallet_amount' => $walletAmount],
            ]);

            if ($storeEarning > 0) {
                \App\Models\OrderFinancialLedger::create([
                    'order_id' => $order->id,
                    'type' => 'STORE_EARNING',
                    'store_id' => $storeId,
                    'amount' => $storeEarning,
                    'entry_type' => 'credit',
                    'description' => "Store earning allocation for order #{$order->order_number}",
                ]);
            }

            if ($totalAdminCommission > 0) {
                \App\Models\OrderFinancialLedger::create([
                    'order_id' => $order->id,
                    'type' => 'ADMIN_COMMISSION',
                    'amount' => $totalAdminCommission,
                    'entry_type' => 'credit',
                    'description' => "Platform commission for order #{$order->order_number}",
                ]);
            }

            if ($driverEarning > 0) {
                \App\Models\OrderFinancialLedger::create([
                    'order_id' => $order->id,
                    'type' => 'DRIVER_EARNING',
                    'amount' => $driverEarning,
                    'entry_type' => 'credit',
                    'description' => "Driver earning allocation (Delivery + Tip) for order #{$order->order_number}",
                    'metadata' => ['delivery_fee' => $shipping, 'tip' => $driverTip],
                ]);
            }

            $order->order_number = (string) (1000 + $order->id);
            $order->save();
            
            // Record Coupon Usage
            if ($appliedCoupon) {
                $appliedCoupon->increment('used_count');
                CouponUsage::create([
                    'coupon_id' => $appliedCoupon->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
            }
            
            if ($shipping == 0 && ($deliveryResult['strategy_used'] ?? '') === 'referral_reward') {
                $this->referralService->consumeFreeDelivery($user);
            }
            
            if (isset($deliveryResult['log_id'])) {
                DeliveryCalculationLog::where('id', $deliveryResult['log_id'])
                    ->update(['order_id' => $order->id]);
            }
            
            foreach ($items as $item) {
                $order->items()->create($item);
            }
            
            // Fix: Clear cart inside the transaction to ensure atomicity
            if ($shouldClearCart) {
                $this->cartService->clearCart($user);
            }
            
            // BACKGROUND SHIELD: Move real-time notifications out of the transaction
            dispatch(function() use ($order, $items, $user, $walletAmount, $remainingAmount, $needsProductPurge) {
                $this->realtimeService->newOrder([
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'wallet_amount' => $walletAmount,
                    'remaining_amount' => $remainingAmount,
                    'customer_name' => $user->name,
                    'items_count' => count($items),
                ]);

                // SMART PURGE: Only clear main product caches if stock levels are critical
                if ($needsProductPurge) {
                    Cache::forget("products_featured_v1_10"); 
                    Cache::forget("products_featured_v1_20");
                    // Add other critical keys here
                }
                
                Cache::forget("orders_user_{$user->id}_p1");
            })->afterResponse();
            
            Cache::forget('orders_admin_recent');
            
            return $order->load(['items.product.primaryImage', 'user', 'address']);
        });
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $oldStatus = $order->status;

        // 1. Lock DELIVERED and CANCELLED orders
        if (in_array($oldStatus, [OrderStatus::DELIVERED, OrderStatus::CANCELLED])) {
            throw new \Exception("Cannot change status of a " . strtoupper($oldStatus->value) . " order.");
        }

        // 2. Enforce forward-only transitions (simplified logic)
        // Hierarchy: pending < confirmed < processing < packed < picked_up < out_for_delivery < delivered
        $statusPriority = [
            OrderStatus::PENDING->value => 1,
            OrderStatus::CONFIRMED->value => 2,
            OrderStatus::PROCESSING->value => 3,
            OrderStatus::PACKED->value => 4,
            OrderStatus::PICKED_UP->value => 5,
            OrderStatus::OUT_FOR_DELIVERY->value => 6,
            OrderStatus::DELIVERED->value => 7,
            OrderStatus::CANCELLED->value => 0, // Special case
        ];

        if ($status !== OrderStatus::CANCELLED) {
             $newPriority = $statusPriority[$status->value] ?? 99;
             $oldPriority = $statusPriority[$oldStatus->value] ?? 0;
             if ($newPriority <= $oldPriority) {
                 throw new \Exception("Invalid status transition: Cannot move from " . strtoupper($oldStatus->value) . " to " . strtoupper($status->value));
             }
        }
        
        // Set timestamp for the new status
        $timestampMap = [
            OrderStatus::CONFIRMED->value => 'confirmed_at',
            OrderStatus::PROCESSING->value => 'processing_at',
            OrderStatus::PACKED->value => 'packed_at',
            OrderStatus::PICKED_UP->value => 'picked_up_at',
            OrderStatus::OUT_FOR_DELIVERY->value => 'out_for_delivery_at',
            OrderStatus::DELIVERED->value => 'delivered_at',
            OrderStatus::CANCELLED->value => 'cancelled_at',
        ];

        $updateData = ['status' => $status];
        if (isset($timestampMap[$status->value])) {
            $updateData[$timestampMap[$status->value]] = now();
        }

        $order->update($updateData);
        
        if ($status === OrderStatus::DELIVERED && $oldStatus !== OrderStatus::DELIVERED) {
            DB::transaction(function () use ($order) {
                // Award Cashback Points
                $this->pointsService->earnPointsForOrder($order);

                // Mark payment_status as paid upon successful delivery
                if ($order->payment_status !== 'paid') {
                    $order->payment_status = 'paid';
                    $order->save();
                }

                // Check for Referral Rewards (on first successful order)
                $deliveredOrdersCount = $order->user->orders()
                    ->where('status', OrderStatus::DELIVERED)
                    ->count();
                if ($deliveredOrdersCount <= 1) {
                    $this->referralService->processReferralRewards($order->user);
                }

                $store = $order->store;
                if ($store) {
                    $order->loadMissing('items');

                    $storeSubtotal = $order->items->sum('total');
                    $store->increment('total_revenue', $storeSubtotal);
                    
                    // Use accurately calculated store_earning if present, or fallback to net formula
                    $netAmount = (float) ($order->store_earning ?? 0);
                    if ($netAmount <= 0) {
                        $totalCommission = $order->items->sum('commission');
                        $netAmount = max(0, ($storeSubtotal - $order->discount) - $totalCommission);
                    }

                    if ($netAmount > 0) {
                        $store->increment('payout_balance', $netAmount);
                    }
                    
                    $store->increment('order_count');
                }

                // Credit Delivery Partner wallet (Delivery Fee + 100% Customer Tip)
                if ($order->delivery_partner_id && $order->order_type !== 'pickup') {
                    $deliveryFee = (float) $order->delivery_fee;
                    $driverTip = (float) ($order->driver_tip ?? 0);
                    $partnerTaxes = $deliveryFee > 0 ? $this->taxService->calculateForPartner($deliveryFee) : 0.0;
                    $netDeliveryFee = max(0, $deliveryFee - $partnerTaxes);
                    $totalDriverAmount = $netDeliveryFee + $driverTip;

                    if ($totalDriverAmount > 0) {
                        $deliveryPartner = $order->deliveryPartner;
                        if ($deliveryPartner) {
                            $wallet = $this->walletService->getOrCreateWallet($deliveryPartner);
                            $this->walletService->creditBalance(
                                $wallet,
                                $totalDriverAmount,
                                'delivery_earning',
                                "Earning (Delivery + Tip) for order #{$order->order_number}",
                                ['delivery_fee' => $netDeliveryFee, 'tip' => $driverTip]
                            );
                            $order->deliveryPartner()->increment('payout_balance', $totalDriverAmount);
                        }
                    }

                    // Track COD Cash Collection liability for Driver
                    if ($order->payment_method === 'cod') {
                        $deliveryPartner = $order->deliveryPartner;
                        if ($deliveryPartner) {
                            $cashCollected = (float) $order->total;
                            $deliveryPartner->increment('cash_in_hand', $cashCollected);
                        }
                    }
                }
            });
        }
        
        // Broadcast status change
        $this->realtimeService->orderStatusChanged([
            'order_id' => $order->id,
            'id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus->value,
            'new_status' => $status->value,
            'user_id' => $order->user_id,
        ]);
        
        // Bust the relevant caches
        Cache::forget("orders_user_{$order->user_id}_p1");
        Cache::forget('orders_admin_recent');

        return $order->fresh();
    }

    public function cancel(Order $order, ?string $reason = null): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled after it has been packed or delivered.');
        }
        
        return DB::transaction(function () use ($order, $reason) {
            $oldStatus = $order->status;

            // Restore stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }
            
            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);
            
            // Process refund
            $this->processRefund($order, (float)$order->total, 'Order Cancellation');
            
            // Notify all parties via FCM (customer, store, assigned driver)
            $this->realtimeService->orderStatusChanged([
                'order_id' => $order->id,
                'id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus->value,
                'new_status' => OrderStatus::CANCELLED->value,
                'user_id' => $order->user_id,
            ]);

            // Also broadcast the cancellation event for admin SSE dashboard
            $this->realtimeService->orderCancelled([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $reason,
            ]);
            
            Cache::forget("orders_user_{$order->user_id}_p1");
            Cache::forget('orders_admin_recent');
            
            return $order->fresh();
        });
    }

    /**
     * Remove item from order and adjust bill/refund
     */
    public function removeItem(Order $order, int $orderItemId): Order
    {
        // Validation: Can only remove items if order is not yet picked up
        if (in_array($order->status, [OrderStatus::PICKED_UP, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED, OrderStatus::CANCELLED])) {
            throw new \Exception('Cannot remove items from an order that is already picked up, delivered or cancelled.');
        }

        return DB::transaction(function () use ($order, $orderItemId) {
            $item = $order->items()->findOrFail($orderItemId);
            
            $itemTotal = (float) $item->total;
            
            // Recalculate tax using TaxService instead of proportional calculation
            $oldTax = $order->tax;
            
            // Temporary map of remaining items
            $remainingItems = $order->items->filter(fn($oi) => $oi->id != $item->id)->map(function ($oi) {
                return [
                    'product_id' => $oi->product_id,
                    'quantity' => $oi->quantity,
                    'price' => $oi->price,
                    'tax_rate' => $oi->product->tax_rate ?? 0,
                ];
            })->toArray();

            $newSubtotal = (float)$order->subtotal - $itemTotal;
            $newTax = $this->taxService->calculateForOrder($remainingItems, $newSubtotal, (float)$order->discount, $order->store_id);
            $taxDeduction = $oldTax - $newTax;

            $totalDeduction = $itemTotal + $taxDeduction;
            
            // Restore stock
            if ($item->product) {
                $item->product->increment('quantity', $item->quantity);
            }
            
            // Remove item
            $item->delete();
            
            // Update Order Totals
            $order->setAttribute('subtotal', bcsub((string)$order->subtotal, (string)$itemTotal, 2));
            $order->setAttribute('tax', bcsub((string)$order->tax, (string)$taxDeduction, 2));
            $order->setAttribute('total', bcsub((string)$order->total, (string)$totalDeduction, 2));
            
            $order->save();
            
            // Process Refund or Bill Adjustment
            $this->processRefund($order, $totalDeduction, "Item Removal: {$item->product_name}");
            
            $order->refresh();
            
            // Notify User directly (avoid template system which would send 'Order Placed')
            $user = $order->user;
            if ($user && $user->fcm_token) {
                $this->realtimeService->getFcmService()->sendToDevice($user->fcm_token, [
                    'title' => 'Order Updated',
                    'body' => "Item '{$item->product_name}' removed from your order #{$order->order_number}. Bill updated to " . \App\Helpers\CurrencyHelper::format((float)$order->total),
                ], [
                    'type' => 'order_update',
                    'order_id' => (string)$order->id,
                    'screen' => 'order_detail',
                    'id' => (string)$order->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            }
            
            return $order;
        });
    }

    /**
     * Update item quantity in order (decrease only) and adjust bill/refund
     */
    public function updateItemQuantity(Order $order, int $orderItemId, int $newQuantity): Order
    {
        if (in_array($order->status, [OrderStatus::PICKED_UP, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED, OrderStatus::CANCELLED])) {
            throw new \Exception('Cannot modify items in an order that is already picked up, delivered or cancelled.');
        }

        if ($newQuantity < 1) {
            throw new \Exception('Quantity must be at least 1. Use remove item to delete entirely.');
        }

        return DB::transaction(function () use ($order, $orderItemId, $newQuantity) {
            $item = $order->items()->findOrFail($orderItemId);

            if ($newQuantity >= $item->quantity) {
                throw new \Exception('New quantity must be less than current quantity. Current: ' . $item->quantity);
            }

            $reducedQty = $item->quantity - $newQuantity;
            $pricePerUnit = (float) $item->price;
            $amountReduced = $pricePerUnit * $reducedQty;

            // Recalculate tax using TaxService instead of hardcoded 10%
            $oldTax = $order->tax;
            
            // Temporary map for tax calculation
            $remainingItems = $order->items->map(function ($oi) use ($item, $newQuantity) {
                return [
                    'product_id' => $oi->product_id,
                    'quantity' => $oi->id == $item->id ? $newQuantity : $oi->quantity,
                    'price' => $oi->price,
                    'tax_rate' => $oi->product->tax_rate ?? 0,
                ];
            })->toArray();
            
            $newSubtotal = (float)$order->subtotal - $amountReduced;
            $newTax = $this->taxService->calculateForOrder($remainingItems, $newSubtotal, (float)$order->discount, $order->store_id);
            $taxDeduction = $oldTax - $newTax;
            
            $totalDeduction = $amountReduced + $taxDeduction;

            // Restore stock for reduced quantity
            if ($item->product) {
                $item->product->increment('quantity', $reducedQty);
            }

            // Update item quantity and total
            $item->quantity = $newQuantity;
            $item->total = $pricePerUnit * $newQuantity;
            $item->save();

            // Update Order Totals
            $order->setAttribute('subtotal', bcsub((string)$order->subtotal, (string)$amountReduced, 2));
            $order->setAttribute('tax', bcsub((string)$order->tax, (string)$taxDeduction, 2));
            $order->setAttribute('total', bcsub((string)$order->total, (string)$totalDeduction, 2));
            $order->save();

            // Process Refund
            $this->processRefund($order, $totalDeduction, "Quantity reduced: {$item->product_name} (x{$reducedQty})");

            $order->refresh();

            // Notify User directly (avoid template system which would send 'Order Placed')
            $user = $order->user;
            if ($user && $user->fcm_token) {
                $this->realtimeService->getFcmService()->sendToDevice($user->fcm_token, [
                    'title' => 'Order Updated',
                    'body' => "Quantity of '{$item->product_name}' in order #{$order->order_number} reduced to {$newQuantity}. Bill updated to " . \App\Helpers\CurrencyHelper::format((float)$order->total),
                ], [
                    'type' => 'order_update',
                    'order_id' => (string)$order->id,
                    'screen' => 'order_detail',
                    'id' => (string)$order->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            }

            return $order;
        });
    }

    /**
     * Process refund for order
     * Handles both Wallet (online) and COD (bill adjustment)
     */
    private function processRefund(Order $order, float $amount, string $reason): void
    {
        // 1. If paid via Wallet/Online -> Refund to Wallet
        // Check if there is a wallet amount involved, or if payment method is 'online' (assuming external gateway mocked via wallet for now)
        // Or if the order has `wallet_amount` used.
        
        $amountToRefundToWallet = 0.0;
        
        if ($order->payment_method !== 'cod') {
             // Online payment: Full refund to wallet
             $amountToRefundToWallet = $amount;
        } elseif ($order->wallet_amount > 0) {
             // Partial Wallet + COD: Refund up to the amount paid by wallet
             // This logic can be complex. Simple approach: Refund to wallet first if any wallet balance was used?
             // Actually, if we remove an item, and user paid partial wallet, we should refund to wallet.
             // But we don't know exactly if *this* item was covered by wallet.
             // Let's assume proportional or priority refund.
             // Simpler: Just refund to wallet if payment method was NOT COD. 
             // If COD, just reducing the total (done above) is enough for the bill.
             
             // If it was partial wallet payment (COD + Wallet), we refund to wallet up to the deducted amount.
             $amountToRefundToWallet = min($amount, $order->wallet_amount);
             
             // Decrease order's recorded wallet usage?
             $order->decrement('wallet_amount', $amountToRefundToWallet);
        }

        if ($amountToRefundToWallet > 0) {
            try {
                $this->walletService->creditBalance(
                    $order->user->wallet,
                    $amountToRefundToWallet,
                    'refund',
                    "Refund for Order #{$order->order_number}: $reason"
                );
            } catch (\Exception $e) {
                Log::error('Refund Failed', ['error' => $e->getMessage()]);
            }
        }
        
        // If COD, the $order->total update is sufficient as the driver will collect the new total.
    }

    public function getUserOrders(User $user, int $perPage = 15)
    {
        return Order::where('user_id', $user->id)
            ->with(['items.product.primaryImage', 'address'])
            ->latest()
            ->paginate($perPage);
    }

    public function getAdminOrders(array $filters = [], int $perPage = 15)
    {
        $query = Order::with(['user', 'items']);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$filters['search']}%"));
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->latest()->paginate($perPage);
    }

    private function calculateDiscount(string $couponCode, float $subtotal, User $user): float
    {
        $coupon = Coupon::where('code', $couponCode)->first();
        if ($coupon && $coupon->canBeUsedBy($user)) {
            return $coupon->calculateDiscount($subtotal);
        }
        return 0;
    }

    /**
     * Calculate shipping cost using store's delivery configuration
     * 
     * @param int|null $storeId Store ID (null for global products)
     * @param int|null $addressId Address ID for zone-based delivery
     * @param float $subtotal Order subtotal
     * @param float|null $distance Distance in kilometers (for per_km calculation)
     * @return float Calculated shipping cost
     */
    private function calculateShipping(?int $storeId, ?int $addressId, float $subtotal, ?float $distance = null): float
    {
        // If no store ID, use global delivery charge
        if (!$storeId) {
            $globalCharge = Setting::get('global_delivery_charge', 5.99);
            return (float) $globalCharge;
        }

        // Load store and use its delivery configuration
        $store = Store::find($storeId);
        
        if (!$store) {
            // Fallback to global delivery charge if store not found
            $globalCharge = Setting::get('global_delivery_charge', 5.99);
            return (float) $globalCharge;
        }

        // Check for zone-based delivery if address provided
        if ($addressId) {
            $zoneDeliveryFee = $this->calculateZoneBasedDelivery($store, $addressId, $distance);
            if ($zoneDeliveryFee !== null) {
                return $zoneDeliveryFee;
            }
        }

        // Use store's calculateDeliveryCharge method
        return $store->calculateDeliveryCharge($subtotal, $distance);
    }

    /**
     * Calculate zone-based delivery fee
     * 
     * @param Store $store The store
     * @param int $addressId Address ID
     * @param float|null $distance Distance in kilometers
     * @return float|null Zone delivery fee or null if no zone match
     */
    /**
     * Calculate zone-based delivery fee for an order.
     * Uses a single spatial SQL query — no N+1.
     */
    private function calculateZoneBasedDelivery(Store $store, int $addressId, ?float $distance = null): ?float
    {
        $address = Address::find($addressId);

        if (!$address || !$address->latitude || !$address->longitude) {
            return null;
        }

        // Single spatial query — ST_Contains in SQL, not PHP loop
        $zone = Zone::active()
            ->covering((float)$address->latitude, (float)$address->longitude)
            ->first();

        if (!$zone) {
            return null;
        }

        $storeZone = $store->zones()
            ->wherePivot('zone_id', $zone->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$storeZone) {
            return null;
        }

        if ($storeZone->pivot->delivery_fee_override !== null) {
            return (float) $storeZone->pivot->delivery_fee_override;
        }

        if ($distance !== null) {
            // calculateDeliveryFee returns an array — extract the total
            $feeDetails = $zone->calculateDeliveryFee($distance);
            return (float) $feeDetails['total_cost'];
        }

        return (float) $zone->base_delivery_fee;
    }

    /**
     * Create multiple orders for multi-vendor cart.
     * Groups items by store and creates a separate order for each store.
     *
     * Returns an array of Orders when items span multiple stores, or a single
     * Order when all items belong to one store (even in multi-vendor mode).
     * The controller uses is_array() to distinguish the two cases, so returning
     * a single Order here ensures is_multi_order is correctly set to false.
     */
    private function createMultiVendorOrders(User $user, array $data): Order|array
    {
        return DB::transaction(function () use ($user, $data) {
            $useWallet = !empty($data['use_wallet']) && $data['use_wallet'];

            // ── Step 1: group items by store ──────────────────────────────────
            $itemsByStore = [];
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $storeId = $product->store_id ?? 0;
                $itemsByStore[$storeId][] = [
                    'product'    => $product,
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity'   => $item['quantity'],
                ];
            }

            // ── Fast path: all items from one store ───────────────────────────
            // No need to split into sub-orders. Delegate to createSingleOrder
            // and return an Order (not an array) so the controller sets
            // is_multi_order: false.
            if (count($itemsByStore) === 1) {
                $order = $this->createSingleOrder($user, $data);
                return $order;
            }

            // ── Step 2: pre-calculate each store's total ──────────────────────
            $storeTotals = [];
            $grandTotal  = 0.0;

            foreach ($itemsByStore as $storeId => $storeItems) {
                $subtotal = 0.0;
                $items    = [];
                foreach ($storeItems as $si) {
                    $itemTotal  = $si['product']->price * $si['quantity'];
                    $subtotal  += $itemTotal;
                    $items[]    = [
                        'product_id' => $si['product']->id,
                        'quantity'   => $si['quantity'],
                        'price'      => $si['product']->price,
                        'tax_rate'   => $si['product']->tax_rate ?? 0,
                        'total'      => $itemTotal,
                    ];
                }

                $deliveryResult = $this->deliveryCalculationService->calculate([
                    'store_id'    => $storeId ?: null,
                    'address_id'  => $data['address_id'] ?? null,
                    'subtotal'    => $subtotal,
                    'distance'    => $data['distance'] ?? null,
                    'user_id'     => $user->id,
                    'coupon_code' => $data['coupon_code'] ?? null,
                ]);

                $tax      = $this->taxService->calculateForOrder($items, $subtotal, 0, $storeId ?: null);
                $shipping = $deliveryResult['final_charge'];
                $total    = $subtotal + $shipping + $tax;

                $storeTotals[$storeId] = [
                    'subtotal'       => $subtotal,
                    'shipping'       => $shipping,
                    'tax'            => $tax,
                    'total'          => $total,
                    'deliveryResult' => $deliveryResult,
                ];
                $grandTotal += $total;
            }

            // ── Step 3: debit wallet ONCE for the grand total ─────────────────
            $walletAmountTotal    = 0.0;
            $remainingAmountTotal = $grandTotal;

            if ($useWallet) {
                $tempOrder   = new Order(['order_number' => 'MV-TEMP', 'total' => $grandTotal]);
                $walletResult = $this->walletService->processOrderPayment(
                    $user,
                    $tempOrder,
                    $grandTotal,
                    true,
                    $data['payment_method']
                );
                $walletAmountTotal    = $walletResult['wallet_amount'];
                $remainingAmountTotal = $walletResult['remaining_amount'];
            }

            // ── Step 4: create each store order, injecting its wallet share ───
            $orders = [];

            foreach ($itemsByStore as $storeId => $storeItems) {
                $st = $storeTotals[$storeId];

                $storeWalletAmount = $grandTotal > 0
                    ? round($walletAmountTotal * ($st['total'] / $grandTotal), 2)
                    : 0.0;

                $storeOrderData = array_merge($data, [
                    'items'      => array_map(fn($si) => [
                        'product_id' => $si['product']->id,
                        'variant_id' => $si['variant_id'] ?? null,
                        'quantity'   => $si['quantity'],
                    ], $storeItems),
                    'use_wallet'            => false,
                    '_precomputed_wallet'   => $storeWalletAmount,
                    '_precomputed_remaining'=> $st['total'] - $storeWalletAmount,
                ]);

                $order = $this->createSingleOrder($user, $storeOrderData, false);
                $orders[] = $order;
            }

            // Clear cart once after all sub-orders are created
            $this->cartService->clearCart($user);

            return $orders;
        });
    }
}
