<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerAdPlan;
use App\Models\SellerAdPurchase;
use App\Services\StorageService;
use Illuminate\Http\Request;

class SellerAdPlanApiController extends Controller
{
    public function __construct(private StorageService $storage) {}

    /**
     * Get active seller ad plans & store active campaigns
     */
    public function index(Request $request)
    {
        $store = $request->user()->store ?? $request->user()->ownedStore;
        $plans = SellerAdPlan::where('status', true)->orderBy('sort_order', 'asc')->get();

        $purchases = [];
        if ($store) {
            $purchases = SellerAdPurchase::with(['plan', 'product'])
                ->where('store_id', $store->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => $plans,
                'purchases' => $purchases,
            ],
        ]);
    }

    /**
     * Purchase an Ad Plan
     */
    public function purchase(Request $request, SellerAdPlan $plan)
    {
        $store = $request->user()->store ?? $request->user()->ownedStore;
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found'], 404);
        }

        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'banner_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'target_url' => 'nullable|string',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $this->storage->store($request->file('banner_image'), 'ads/banners/' . $store->id);
        }

        $purchase = SellerAdPurchase::create([
            'seller_ad_plan_id' => $plan->id,
            'store_id' => $store->id,
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'] ?? null,
            'banner_image' => $bannerPath,
            'target_url' => $validated['target_url'] ?? null,
            'amount_paid' => $plan->price,
            'payment_status' => 'paid',
            'status' => 'pending', // Pending Admin approval
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Campaign purchased for '{$plan->name}'! Pending Admin approval.",
            'data' => $purchase->load(['plan', 'store']),
        ]);
    }
}
