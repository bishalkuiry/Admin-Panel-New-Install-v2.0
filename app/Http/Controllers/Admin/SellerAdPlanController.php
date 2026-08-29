<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerAdPlan;
use App\Models\SellerAdPurchase;
use Illuminate\Http\Request;

class SellerAdPlanController extends Controller
{
    public function index()
    {
        $plans = SellerAdPlan::withCount('purchases')->orderBy('sort_order', 'asc')->paginate(15);
        $totalRevenue = SellerAdPurchase::where('payment_status', 'paid')->sum('amount_paid');
        $activeCampaigns = SellerAdPurchase::where('status', 'active')->count();

        return view('admin.advertisements.plans.index', compact('plans', 'totalRevenue', 'activeCampaigns'));
    }

    public function create()
    {
        return view('admin.advertisements.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ad_type' => 'required|string|in:banner_ad,featured_store,sponsored_product',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_type' => 'required|string|in:one_time,daily,weekly,monthly',
            'duration_days' => 'required|integer|min:1',
            'placement' => 'required|string',
            'impression_limit' => 'nullable|integer|min:1',
            'click_limit' => 'nullable|integer|min:1',
            'priority_level' => 'required|integer|min:1',
            'targeting_options' => 'nullable|array',
            'banner_size' => 'nullable|string',
            'max_items' => 'required|integer|min:1',
            'auto_renew' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        $validated['status'] = $request->has('status');

        SellerAdPlan::create($validated);

        return redirect()->route('admin.ad-plans.index')->with('success', 'Seller advertisement plan created successfully.');
    }

    public function show(SellerAdPlan $adPlan)
    {
        return redirect()->route('admin.ad-plans.edit', $adPlan);
    }

    public function edit(SellerAdPlan $adPlan)
    {
        return view('admin.advertisements.plans.edit', ['plan' => $adPlan]);
    }

    public function update(Request $request, SellerAdPlan $adPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ad_type' => 'required|string|in:banner_ad,featured_store,sponsored_product',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_type' => 'required|string|in:one_time,daily,weekly,monthly',
            'duration_days' => 'required|integer|min:1',
            'placement' => 'required|string',
            'impression_limit' => 'nullable|integer|min:1',
            'click_limit' => 'nullable|integer|min:1',
            'priority_level' => 'required|integer|min:1',
            'targeting_options' => 'nullable|array',
            'banner_size' => 'nullable|string',
            'max_items' => 'required|integer|min:1',
            'auto_renew' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        $validated['status'] = $request->has('status');

        $adPlan->update($validated);

        return redirect()->route('admin.ad-plans.index')->with('success', 'Seller advertisement plan updated successfully.');
    }

    public function destroy(SellerAdPlan $adPlan)
    {
        $adPlan->delete();
        return redirect()->route('admin.ad-plans.index')->with('success', 'Seller advertisement plan deleted successfully.');
    }

    public function purchases()
    {
        $purchases = SellerAdPurchase::with(['plan', 'store', 'product', 'user'])->latest()->paginate(20);
        return view('admin.advertisements.plans.purchases', compact('purchases'));
    }

    public function updatePurchaseStatus(Request $request, SellerAdPurchase $purchase)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:active,rejected,expired,pending',
            'rejection_reason' => 'nullable|string',
        ]);

        if ($validated['status'] === 'active' && !$purchase->starts_at) {
            $purchase->starts_at = now();
            $purchase->expires_at = now()->addDays($purchase->plan->duration_days ?? 7);
            $purchase->payment_status = 'paid';
        }

        $purchase->update($validated);

        return redirect()->back()->with('success', 'Seller campaign status updated successfully.');
    }
}
