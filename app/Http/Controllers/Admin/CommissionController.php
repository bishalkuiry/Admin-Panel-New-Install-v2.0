<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Store;
use App\Models\Setting;
use App\Services\TaxService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function __construct(private TaxService $taxService) {}

    /**
     * List all commission rules (store + delivery_partner)
     */
    public function index(Request $request)
    {
        $storeCommissions = Tax::with(['store', 'storeCategory'])
            ->where('applies_to', 'store')
            ->latest()
            ->get();

        $partnerCommissions = Tax::where('applies_to', 'delivery_partner')
            ->latest()
            ->get();

        $stats = [
            'store_rules' => $storeCommissions->count(),
            'store_active' => $storeCommissions->where('is_active', true)->count(),
            'partner_rules' => $partnerCommissions->count(),
            'partner_active' => $partnerCommissions->where('is_active', true)->count(),
        ];

        // NEW: Global and Store Base Rates
        $globalCommission = Setting::get('default_commission_percent', 10);
        
        $query = Store::query()->orderBy('name');
        if ($request->query('search')) {
            $query->where('name', 'like', '%' . $request->query('search') . '%');
        }
        $stores = $query->paginate(15)->withQueryString();

        return view('admin.commissions.index', compact(
            'storeCommissions', 
            'partnerCommissions', 
            'stats', 
            'globalCommission', 
            'stores'
        ));
    }

    /**
     * Update global default commission
     */
    public function updateGlobal(Request $request)
    {
        $request->validate([
            'default_commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        Setting::set('default_commission_percent', $request->input('default_commission_percent'), 'general');

        return back()->with('success', 'Global default commission updated.');
    }

    /**
     * Update a specific store's base commission
     */
    public function updateStoreBase(Request $request, Store $store)
    {
        $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        $store->update(['commission_percent' => $request->commission_percent]);
        
        return back()->with('success', "Base commission for {$store->name} updated.");
    }

    /**
     * Show the create commission form
     */
    public function create(Request $request)
    {
        $type = $request->query('type', 'store'); // store or delivery_partner
        $stores = Store::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.commissions.create', compact('type', 'stores'));
    }

    /**
     * Store a new commission rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'applies_to'        => 'required|in:store,delivery_partner',
            'calculation_type'  => 'required|in:fixed,percentage',
            'value'             => 'required|numeric|min:0',
            'store_id'          => 'required_if:applies_to,store|nullable|exists:stores,id',
            'store_category_id' => 'nullable|exists:categories,id',
            'is_active'         => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_inclusive'] = false;
        $validated['min_order_value'] = null;
        $validated['max_order_value'] = null;

        // Clear irrelevant fields
        if ($validated['applies_to'] !== 'store') {
            $validated['store_id'] = null;
            $validated['store_category_id'] = null;
        }

        Tax::create($validated);
        $this->taxService->clearCache();

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission rule created successfully.');
    }

    /**
     * Edit a commission rule
     */
    public function edit(Tax $commission)
    {
        $stores = Store::active()->orderBy('name')->get(['id', 'name']);
        return view('admin.commissions.edit', compact('commission', 'stores'));
    }

    /**
     * Update a commission rule
     */
    public function update(Request $request, Tax $commission)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'applies_to'        => 'required|in:store,delivery_partner',
            'calculation_type'  => 'required|in:fixed,percentage',
            'value'             => 'required|numeric|min:0',
            'store_id'          => 'required_if:applies_to,store|nullable|exists:stores,id',
            'store_category_id' => 'nullable|exists:categories,id',
            'is_active'         => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['applies_to'] !== 'store') {
            $validated['store_id'] = null;
            $validated['store_category_id'] = null;
        }

        $commission->update($validated);
        $this->taxService->clearCache();

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission rule updated successfully.');
    }

    /**
     * Delete a commission rule
     */
    public function destroy(Tax $commission)
    {
        $commission->delete();
        $this->taxService->clearCache();

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission rule deleted.');
    }

    /**
     * Toggle active status
     */
    public function toggle(Tax $commission)
    {
        $commission->update(['is_active' => !$commission->is_active]);
        $this->taxService->clearCache();

        return back()->with('success', 'Commission rule ' . ($commission->is_active ? 'enabled' : 'disabled') . '.');
    }

    /**
     * AJAX: Return stores for dropdown
     */
    public function stores()
    {
        $stores = Store::active()->orderBy('name')->get(['id', 'name']);
        return response()->json($stores);
    }

    /**
     * AJAX: Return categories for a specific store
     */
    public function storeCategories(Store $store)
    {
        $categoryIds = $store->products()
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }
}
