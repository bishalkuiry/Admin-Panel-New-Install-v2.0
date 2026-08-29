<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSubscriptionPlan;
use App\Models\StoreSubscription;
use App\Models\Store;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = StoreSubscriptionPlan::withCount('subscriptions')->latest()->paginate(15);
        $activeSubscriptions = StoreSubscription::with(['store', 'plan'])->where('status', 'active')->latest()->take(10)->get();

        return view('admin.subscriptions.index', compact('plans', 'activeSubscriptions'));
    }

    public function create()
    {
        return view('admin.subscriptions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|string|in:monthly,yearly,trial',
            'duration_days' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'trial_period_days' => 'nullable|integer|min:0',
            'auto_renew' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        $validated['is_active'] = $request->has('is_active');
        $validated['trial_period_days'] = $validated['trial_period_days'] ?? 0;

        StoreSubscriptionPlan::create($validated);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription plan created successfully.');
    }

    public function show(StoreSubscriptionPlan $subscription)
    {
        return redirect()->route('admin.subscriptions.edit', $subscription);
    }

    public function edit(StoreSubscriptionPlan $subscription)
    {
        return view('admin.subscriptions.edit', ['plan' => $subscription]);
    }

    public function update(Request $request, StoreSubscriptionPlan $subscription)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|string|in:monthly,yearly,trial',
            'duration_days' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'trial_period_days' => 'nullable|integer|min:0',
            'auto_renew' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        $validated['is_active'] = $request->has('is_active');
        $validated['trial_period_days'] = $validated['trial_period_days'] ?? 0;

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(StoreSubscriptionPlan $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription plan deleted successfully.');
    }
}
