<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSubscription;
use App\Models\StoreSubscriptionPlan;
use Illuminate\Http\Request;

class StoreSubscriptionController extends Controller
{
    public function index()
    {
        $plans = StoreSubscriptionPlan::latest()->get();
        $subscriptions = StoreSubscription::with(['store', 'plan'])->latest()->paginate(20);

        return view('admin.subscriptions.store', compact('plans', 'subscriptions'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        StoreSubscriptionPlan::create($validated);

        return redirect()->back()->with('success', 'Store Subscription Plan created successfully!');
    }

    public function destroyPlan(StoreSubscriptionPlan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Subscription Plan deleted!');
    }
}
