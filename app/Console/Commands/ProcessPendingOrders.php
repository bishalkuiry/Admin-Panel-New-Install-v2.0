<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use App\Services\RealtimeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingOrders extends Command
{
    protected $signature = 'orders:process-pending';
    protected $description = 'Auto-cancel or auto-accept pending orders after configured timeout';

    public function handle(OrderService $orderService, RealtimeService $realtimeService): int
    {
        try {
            $timeoutMinutes = (int) Setting::get('order_timeout_minutes', 3);
            $timeoutAction = Setting::get('order_timeout_action', 'auto_cancel');

            $cutoff = now()->subMinutes($timeoutMinutes);

            // Find pending orders older than timeout
            $pendingOrders = Order::where('status', OrderStatus::PENDING)
                ->where('created_at', '<=', $cutoff)
                ->get();

            if ($pendingOrders->isEmpty()) {
                $this->info('No pending orders past timeout.');
                return self::SUCCESS;
            }

            $processed = 0;

            foreach ($pendingOrders as $order) {
                try {
                    if ($timeoutAction === 'auto_cancel') {
                        // Auto cancel + refund
                        $orderService->cancel($order, 'Auto-cancelled: No response within ' . $timeoutMinutes . ' minutes');
                        $this->info("Order #{$order->order_number} auto-cancelled and refunded.");
                        Log::info("Order #{$order->order_number} auto-cancelled after {$timeoutMinutes}min timeout");
                    } else {
                        // Auto accept → set to confirmed
                        $oldStatus = $order->status->value;
                        $order->update(['status' => OrderStatus::CONFIRMED]);

                        $realtimeService->orderStatusChanged([
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldStatus,
                            'new_status' => OrderStatus::CONFIRMED->value,
                            'user_id' => $order->user_id,
                        ]);

                        $this->info("Order #{$order->order_number} auto-accepted.");
                        Log::info("Order #{$order->order_number} auto-accepted after {$timeoutMinutes}min timeout");
                    }

                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Failed to process order #{$order->order_number}: " . $e->getMessage());
                    Log::error("ProcessPendingOrders failed for order #{$order->order_number}: " . $e->getMessage());
                }
            }

            $this->info("Processed {$processed} of {$pendingOrders->count()} pending orders ({$timeoutAction}).");
            return self::SUCCESS;

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database connection errors gracefully (common in local environments with frequent polling)
            if ($e->getCode() === 2002 || str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'Unknown error')) {
                // Log::warning("ProcessPendingOrders: Database connection failed temporarily.");
                return self::SUCCESS;
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error("ProcessPendingOrders failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
