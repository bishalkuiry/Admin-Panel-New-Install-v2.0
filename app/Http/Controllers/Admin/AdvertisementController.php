<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $query = Advertisement::with(['store', 'product'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ad_type')) {
            $query->where('ad_type', $request->ad_type);
        }

        $advertisements = $query->paginate(20);
        $stores = Store::where('status', 'approved')->orderBy('name')->get();

        return view('admin.ads.index', compact('advertisements', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'ad_type' => 'required|in:banner,featured_store,sponsored_product',
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'price' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $validated['status'] = 'active';

        Advertisement::create($validated);

        return redirect()->back()->with('success', 'Paid advertisement package created and published!');
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ad_type' => 'required|in:banner,featured_store,sponsored_product',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        \App\Models\AdPlan::create($validated);

        return redirect()->back()->with('success', 'Advertising Plan created successfully!');
    }

    public function destroyPlan(\App\Models\AdPlan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Advertising Plan deleted!');
    }
}
