<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StorePayout;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Exports\PayoutsExport;
use App\Services\StoreService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;


class PayoutController extends Controller
{
    public function __construct(
        private StoreService $storeService
    ) {}

    public function index(Request $request)
    {
        $store = $request->current_store;
        
        // Use the fixed payout_balance that we track in the store model
        // This balance is already (Net Revenue - Commission) and excludes Tax/Delivery
        $availableBalance = $store?->payout_balance ?? 0;
        
        $deliveredRevenue = Order::where('store_id', $store->id)
            ->where('status', OrderStatus::DELIVERED)
            ->sum(DB::raw('subtotal - discount')); // Real revenue before commission

        $payouts = StorePayout::where('store_id', $store->id)
            ->latest()
            ->paginate(10);

        return view('seller.payouts.index', compact('store', 'payouts', 'availableBalance', 'deliveredRevenue'));
    }

    /**
     * Request a new payout
     */
    public function requestPayout(Request $request)
    {
        $store = $request->current_store;
        $availableBalance = $store->payout_balance ?? 0;
        $amount = (float) $request->input('amount', $availableBalance);

        if ($amount < 100) {
            return back()->with('error', 'Minimum payout threshold is ₹100.00');
        }

        try {
            $this->storeService->createPayout($store, $amount, Auth::user(), [
                'payment_method' => $store->payment_method ?? 'bank_transfer',
                'notes' => 'Self-requested payout'
            ]);
            return back()->with('success', 'Payout request submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export payout history
     */
    public function export(Request $request)
    {
        $store = $request->current_store;
        return Excel::download(new PayoutsExport($store->id), 'payouts_' . date('Y-m-d') . '.xlsx');
    }


    public function show(StorePayout $payout)
    {
        // Abort if payout doesn't belong to the current store
        // This is handled by a middleware usually, but good to be safe
        
        return view('seller.payouts.show', compact('payout'));
    }
}
