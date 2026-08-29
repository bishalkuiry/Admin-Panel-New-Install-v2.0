<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerAdPlan;
use App\Models\SellerAdPurchase;
use Illuminate\Http\Request;

class SellerAdApiController extends Controller
{
    public function getPlans(Request $request)
    {
        $plans = SellerAdPlan::where('status', true)->orderBy('sort_order', 'asc')->get();
        $store = $request->user()->store ?? $request->user()->ownedStore;

        $myCampaigns = collect();
        if ($store) {
            $myCampaigns = SellerAdPurchase::with(['plan', 'product'])
                ->where('store_id', $store->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => $plans,
                'my_campaigns' => $myCampaigns,
            ],
        ]);
    }

    public function purchase(Request $request, SellerAdPlan $plan)
    {
        $store = $request->user()->store ?? $request->user()->ownedStore;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(\App\Services\StorageService::class)->store($request->file('image'), 'seller_ads');
        }

        $campaign = SellerAdPurchase::create([
            'plan_id' => $plan->id,
            'store_id' => $store->id,
            'user_id' => $request->user()->id,
            'product_id' => $request->input('product_id'),
            'title' => $request->input('title', $store->name),
            'image' => $imagePath,
            'target_url' => $request->input('target_url'),
            'payment_status' => 'paid',
            'status' => 'pending',
            'amount_paid' => $plan->price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advertisement campaign submitted for approval.',
            'data' => $campaign->load('plan'),
        ]);
    }
}
