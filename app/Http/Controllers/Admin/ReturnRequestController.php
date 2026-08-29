<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReturn;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReturn::with(['order', 'user', 'store']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $returns = $query->orderBy('id', 'desc')->paginate(20);

        return view('admin.returns.index', compact('returns'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,approved,rejected,pickup_scheduled,picked_up,refunded,completed',
            'admin_notes' => 'nullable|string',
        ]);

        $productReturn = ProductReturn::findOrFail($id);
        $oldStatus = $productReturn->status;
        $productReturn->status = $request->status;
        if ($request->filled('admin_notes')) {
            $productReturn->admin_notes = $request->admin_notes;
        }
        $productReturn->save();

        // Process automatic wallet refund if status set to approved or refunded
        if (in_array($request->status, ['approved', 'refunded']) && !in_array($oldStatus, ['approved', 'refunded'])) {
            DB::transaction(function () use ($productReturn) {
                if ($productReturn->refund_amount > 0) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $productReturn->user_id],
                        ['balance' => 0.00]
                    );
                    $balanceBefore = (float) $wallet->balance;
                    $wallet->balance += $productReturn->refund_amount;
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'user_id' => $productReturn->user_id,
                        'amount' => $productReturn->refund_amount,
                        'type' => 'credit',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'description' => "Admin Refund for Return Request #{$productReturn->return_number}",
                        'reference_id' => $productReturn->id,
                        'reference_type' => ProductReturn::class,
                    ]);

                    $productReturn->status = 'refunded';
                    $productReturn->save();
                }
            });
        }

        $this->sendReturnNotification($productReturn, $productReturn->status);

        return redirect()->back()->with('success', "Return Request #{$productReturn->return_number} status updated to " . ucfirst($request->status));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'type' => 'required|in:return,replacement',
            'reason' => 'nullable|string|max:500',
        ]);

        $order = \App\Models\Order::findOrFail($validated['order_id']);
        $item = \App\Models\OrderItem::findOrFail($validated['order_item_id']);

        try {
            DB::beginTransaction();

            $returnNumber = 'RET-' . strtoupper(uniqid());
            $refundAmount = $item->price * $item->quantity;

            $productReturn = ProductReturn::create([
                'return_number' => $returnNumber,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'user_id' => $order->user_id,
                'store_id' => $order->store_id,
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? ($validated['type'] === 'return' ? 'Admin Initiated Return & Refund' : 'Admin Initiated Replacement'),
                'refund_amount' => $refundAmount,
                'status' => $validated['type'] === 'return' ? 'approved' : 'pending',
                'admin_notes' => 'Created via Admin Order Management',
            ]);

            if ($validated['type'] === 'return') {
                if ($refundAmount > 0) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $order->user_id],
                        ['balance' => 0.00]
                    );
                    $balanceBefore = (float) $wallet->balance;
                    $wallet->balance += $refundAmount;
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'user_id' => $order->user_id,
                        'amount' => $refundAmount,
                        'type' => 'credit',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'description' => "Admin Refund for Order #{$order->order_number} Item: {$item->product_name}",
                        'reference_id' => $productReturn->id,
                        'reference_type' => ProductReturn::class,
                    ]);

                    $productReturn->status = 'refunded';
                    $productReturn->save();
                }
            }

            DB::commit();

            $this->sendReturnNotification($productReturn, $productReturn->status);

            return redirect()->route('admin.returns.index')->with('success', ucfirst($validated['type']) . " request #{$returnNumber} created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create return request: ' . $e->getMessage());
        }
    }

    private function sendReturnNotification(ProductReturn $productReturn, string $status)
    {
        $user = User::find($productReturn->user_id);
        if (!$user) return;

        $formattedStatus = ucfirst(str_replace('_', ' ', $status));
        $title = "Return Request Update (#{$productReturn->return_number})";
        $body = "Your {$productReturn->type} request for #{$productReturn->return_number} has been updated to {$formattedStatus}.";

        if ($status === 'refunded' || $status === 'approved') {
            $title = "💰 Refund Processed (#{$productReturn->return_number})";
            $body = "₹" . number_format($productReturn->refund_amount, 2) . " has been refunded to your wallet for return request #{$productReturn->return_number}.";
        } elseif ($status === 'rejected') {
            $title = "Return Request Rejected (#{$productReturn->return_number})";
            $body = "Your return request #{$productReturn->return_number} has been rejected. Check details in support.";
        } elseif ($status === 'picked_up') {
            $title = "Item Picked Up (#{$productReturn->return_number})";
            $body = "Your returned item for #{$productReturn->return_number} has been picked up successfully.";
        }

        try {
            if ($user->fcm_token) {
                app(\App\Services\FirebaseCloudMessagingService::class)->sendToUser($user, [
                    'title' => $title,
                    'body' => $body,
                ], [
                    'type' => 'return_update',
                    'return_id' => (string) $productReturn->id,
                    'status' => $status,
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\ReturnStatusUpdated',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $title,
                        'message' => $body,
                        'return_id' => $productReturn->id,
                        'type' => 'return_status_update',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send return notification: ' . $e->getMessage());
        }
    }
}
