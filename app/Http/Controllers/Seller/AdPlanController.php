<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerAdPlan;
use App\Models\SellerAdPurchase;
use App\Models\Product;
use Illuminate\Http\Request;

class AdPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $store = $user->store ?? $user->ownedStore;

        $plans = SellerAdPlan::where('status', true)->orderBy('sort_order', 'asc')->get();

        $myPurchases = collect();
        $products = collect();
        if ($store) {
            $myPurchases = SellerAdPurchase::with(['plan', 'product'])
                ->where('store_id', $store->id)
                ->latest()
                ->get();

            $products = Product::where('store_id', $store->id)->get();
        }

        return view('seller.advertisements.plans', compact('store', 'plans', 'myPurchases', 'products'));
    }

    public function purchase(Request $request, SellerAdPlan $plan)
    {
        $user = $request->user();
        $store = $user->store ?? $user->ownedStore;

        if (!$store) {
            return redirect()->back()->with('error', 'Store record not found.');
        }

        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'target_url' => 'nullable|url',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(\App\Services\StorageService::class)->store($request->file('image'), 'seller_ads');
        }

        SellerAdPurchase::create([
            'plan_id' => $plan->id,
            'store_id' => $store->id,
            'user_id' => $user->id,
            'product_id' => $validated['product_id'] ?? null,
            'title' => $validated['title'] ?? $store->name,
            'image' => $imagePath,
            'target_url' => $validated['target_url'] ?? null,
            'payment_status' => $plan->price > 0 ? 'paid' : 'paid',
            'status' => 'pending', // Requires Admin Approval
            'amount_paid' => $plan->price,
        ]);

        return redirect()->back()->with('success', "Campaign submitted for {$plan->name}. Awaiting admin approval.");
    }
}
