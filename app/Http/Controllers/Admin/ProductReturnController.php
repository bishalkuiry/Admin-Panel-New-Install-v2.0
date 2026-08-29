<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReturn;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReturn::with(['order', 'user', 'store'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->paginate(20);

        return view('admin.returns.index', compact('returns'));
    }

    public function show(ProductReturn $productReturn)
    {
        $productReturn->load(['order.items.product', 'user', 'store']);
        return view('admin.returns.show', compact('productReturn'));
    }

    public function updateStatus(Request $request, ProductReturn $productReturn)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,pickup_scheduled,picked_up,refunded,replaced',
            'admin_notes' => 'nullable|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $productReturn->status;
            $productReturn->status = $validated['status'];
            if ($request->filled('admin_notes')) {
                $productReturn->admin_notes = $validated['admin_notes'];
            }
            if ($request->filled('refund_amount')) {
                $productReturn->refund_amount = $validated['refund_amount'];
            }
            $productReturn->save();

            // Handle Automatic Wallet Refund when marked as refunded or approved with refund
            if (($validated['status'] === 'refunded' || ($validated['status'] === 'approved' && $productReturn->refund_amount > 0)) && $oldStatus !== 'refunded') {
                $refundAmount = $productReturn->refund_amount > 0 ? $productReturn->refund_amount : ($productReturn->order->total ?? 0);
                
                if ($refundAmount > 0) {
                    $walletService = app(\App\Services\WalletService::class);
                    $user = $productReturn->user ?? \App\Models\User::find($productReturn->user_id);
                    if ($user) {
                        $wallet = $walletService->getOrCreateWallet($user);
                        $walletService->creditBalance(
                            $wallet,
                            $refundAmount,
                            \App\Models\WalletTransaction::TYPE_REFUND,
                            "Refund for Return Request #{$productReturn->return_number}",
                            [
                                'return_id' => $productReturn->id,
                                'order_id' => $productReturn->order_id,
                                'return_number' => $productReturn->return_number,
                            ]
                        );
                    }

                    // Adjust Store payout_balance proportionally
                    $store = $productReturn->store ?? $productReturn->order?->store;
                    if ($store) {
                        $orderSubtotal = (float) ($productReturn->order->subtotal ?? 1);
                        $storeEarningTotal = (float) ($productReturn->order->store_earning ?? 0);
                        $proportionalStoreDeduction = $orderSubtotal > 0 ? round($refundAmount * ($storeEarningTotal / $orderSubtotal), 2) : 0;
                        if ($proportionalStoreDeduction > 0) {
                            $store->decrement('payout_balance', min($store->payout_balance, $proportionalStoreDeduction));
                        }
                    }

                    // Record Financial Ledger Reversal
                    \App\Models\OrderFinancialLedger::create([
                        'order_id' => $productReturn->order_id,
                        'type' => 'REFUND',
                        'user_id' => $productReturn->user_id,
                        'store_id' => $productReturn->store_id,
                        'amount' => $refundAmount,
                        'entry_type' => 'debit',
                        'description' => "Return Request Refund #{$productReturn->return_number}",
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Return request #{$productReturn->return_number} status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product return status', ['error' => $e->getMessage(), 'id' => $productReturn->id]);
            return redirect()->back()->with('error', 'Failed to update return status: ' . $e->getMessage());
        }
    }
}
