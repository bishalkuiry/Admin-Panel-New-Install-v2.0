<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\StoreSubscriptionPlan;
use App\Models\StoreSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $store = $user->store ?? $user->ownedStore;
        
        $activeSubscription = null;
        if ($store) {
            $activeSubscription = StoreSubscription::with('plan')
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        $plans = StoreSubscriptionPlan::where('is_active', true)->get();

        return view('seller.subscription.index', compact('store', 'activeSubscription', 'plans'));
    }

    public function subscribe(Request $request, StoreSubscriptionPlan $plan)
    {
        $user = $request->user();
        $store = $user->store ?? $user->ownedStore;

        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }

        // Cancel previous active subscriptions
        StoreSubscription::where('store_id', $store->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $startsAt = now();
        $expiresAt = now()->addDays($plan->duration_days);

        StoreSubscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_trial' => $plan->billing_cycle === 'trial',
            'payment_method' => 'wallet',
            'price_paid' => $plan->price,
            'auto_renew' => $plan->auto_renew,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', "Subscribed to {$plan->name} successfully!");
    }
}
