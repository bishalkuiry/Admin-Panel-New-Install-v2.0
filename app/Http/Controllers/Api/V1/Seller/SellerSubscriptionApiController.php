<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Models\StoreSubscriptionPlan;
use App\Models\StoreSubscription;
use Illuminate\Http\Request;

class SellerSubscriptionApiController extends Controller
{
    public function getPlans(Request $request)
    {
        $plans = StoreSubscriptionPlan::where('is_active', true)->get();
        $store = $request->user()->store ?? $request->user()->ownedStore;

        $activeSub = null;
        if ($store) {
            $activeSub = StoreSubscription::with('plan')
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'active_subscription' => $activeSub,
                'plans' => $plans,
            ],
        ]);
    }

    public function subscribe(Request $request, StoreSubscriptionPlan $plan)
    {
        $store = $request->user()->store ?? $request->user()->ownedStore;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        StoreSubscription::where('store_id', $store->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $subscription = StoreSubscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
            'is_trial' => $plan->billing_cycle === 'trial',
            'payment_method' => $request->input('payment_method', 'wallet'),
            'price_paid' => $plan->price,
            'auto_renew' => $plan->auto_renew,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Subscribed to {$plan->name} successfully",
            'data' => $subscription->load('plan'),
        ]);
    }
}
