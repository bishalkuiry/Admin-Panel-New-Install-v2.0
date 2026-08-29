<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Store;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct(private StorageService $storage) {}
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $brands = $query->orderBy('sort_order')->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        $stores = Store::active()->get();
        return view('admin.brands.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'store_ids' => 'nullable|array',
            'store_ids.*' => 'exists:stores,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->storage->store($request->file('logo'), 'brands');
        }

        $brand = Brand::create($validated);

        if (!empty($validated['store_ids'])) {
            $brand->stores()->sync($validated['store_ids']);
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully');
    }

    public function edit(Brand $brand)
    {
        $stores = Store::active()->get();
        $assignedStoreIds = $brand->stores->pluck('id')->toArray();
        return view('admin.brands.edit', compact('brand', 'stores', 'assignedStoreIds'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'store_ids' => 'nullable|array',
            'store_ids.*' => 'exists:stores,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->storage->store($request->file('logo'), 'brands');
        } elseif ($request->boolean('remove_logo')) {
            $validated['logo'] = null;
        }

        $brand->update($validated);

        $brand->stores()->sync($validated['store_ids'] ?? []);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Cannot delete brand with products');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted');
    }

    public function toggleStatus(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $brand->is_active,
        ]);
    }

    public function toggleFeatured(Brand $brand)
    {
        $brand->update(['is_featured' => !$brand->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $brand->is_featured,
        ]);
    }
}
