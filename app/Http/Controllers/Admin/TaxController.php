<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct(private TaxService $taxService) {}

    public function index()
    {
        $taxes = Tax::where('applies_to', 'customer')->latest()->get();
        return view('admin.taxes.index', compact('taxes'));
    }

    public function create()
    {
        return view('admin.taxes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'calculation_type'  => 'required|in:fixed,percentage',
            'value'             => 'required|numeric|min:0',
            'min_order_value'   => 'nullable|numeric|min:0',
            'max_order_value'   => 'nullable|numeric|min:0|gt:min_order_value',
            'is_active'         => 'boolean',
            'is_inclusive'      => 'boolean',
        ]);

        $validated['applies_to'] = 'customer';
        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['is_inclusive'] = $request->boolean('is_inclusive', false);

        Tax::create($validated);
        $this->taxService->clearCache();

        return redirect()->route('admin.taxes.index')
            ->with('success', 'Tax rule created successfully.');
    }

    public function edit(Tax $tax)
    {
        return view('admin.taxes.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'calculation_type'  => 'required|in:fixed,percentage',
            'value'             => 'required|numeric|min:0',
            'min_order_value'   => 'nullable|numeric|min:0',
            'max_order_value'   => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
            'is_inclusive'      => 'boolean',
        ]);

        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['is_inclusive'] = $request->boolean('is_inclusive', false);

        $tax->update($validated);
        $this->taxService->clearCache();

        return redirect()->route('admin.taxes.index')
            ->with('success', 'Tax rule updated successfully.');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        $this->taxService->clearCache();

        return redirect()->route('admin.taxes.index')
            ->with('success', 'Tax rule deleted.');
    }

    public function toggle(Tax $tax)
    {
        $tax->update(['is_active' => !$tax->is_active]);
        $this->taxService->clearCache();

        return back()->with('success', 'Tax rule ' . ($tax->is_active ? 'enabled' : 'disabled') . '.');
    }
}
