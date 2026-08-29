<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\AdPlan;
use App\Models\Advertisement;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdvertisementController extends Controller
{
    public function index()
    {
        $seller = auth()->user();
        $store = $seller->store ?? $seller->stores()->first();

        $plans = AdPlan::where('is_active', true)->latest()->get();
        $myAds = Advertisement::where('store_id', $store?->id)->latest()->get();

        $wallet = Wallet::firstOrCreate(['user_id' => $seller->id], ['balance' => 0.00]);

        return view('seller.ads.index', compact('plans', 'myAds', 'wallet', 'store'));
    }

    public function buyPlan(Request $request, AdPlan $plan)
    {
        $seller = auth()->user();
        $store = $seller->store ?? $seller->stores()->first();

        if (!$store) {
            return redirect()->back()->with('error', 'No active store found to purchase advertisement.');
        }

        $price = (float)$plan->price;
        $wallet = Wallet::firstOrCreate(['user_id' => $seller->id], ['balance' => 0.00]);

        if ($wallet->balance < $price) {
            return redirect()->back()->with('error', "Insufficient wallet balance (Current: ₹{$wallet->balance}). Please top up your wallet to buy this plan (Price: ₹{$price}).");
        }

        try {
            DB::beginTransaction();

            // Deduct Amount from Seller Wallet
            $wallet->balance -= $price;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $seller->id,
                'amount' => $price,
                'type' => 'debit',
                'description' => "Purchased Ad Plan: {$plan->name}",
                'reference_id' => $plan->id,
                'reference_type' => AdPlan::class,
            ]);

            // Create Active Advertisement
            Advertisement::create([
                'store_id' => $store->id,
                'ad_type' => $plan->ad_type,
                'title' => "Ad Campaign: {$plan->name} ({$store->name})",
                'price' => $price,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Ad Plan '{$plan->name}' purchased successfully! Amount ₹{$price} deducted from your wallet.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ad plan purchase failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Purchase failed: ' . $e->getMessage());
        }
    }
}
