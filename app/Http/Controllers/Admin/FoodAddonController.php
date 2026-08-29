<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodAddon;
use Illuminate\Http\Request;

class FoodAddonController extends Controller
{
    public function index(Request $request)
    {
        $addons = FoodAddon::latest()->paginate(15);
        return view('admin.food-addons.index', compact('addons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $addon = FoodAddon::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $addon]);
        }

        return redirect()->route('admin.food-addons.index')->with('success', 'Food add-on created successfully!');
    }

    public function update(Request $request, int $id)
    {
        $addon = FoodAddon::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $addon->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $addon]);
        }

        return redirect()->route('admin.food-addons.index')->with('success', 'Food add-on updated successfully!');
    }

    public function destroy(int $id)
    {
        $addon = FoodAddon::findOrFail($id);
        $addon->delete();

        return response()->json(['success' => true, 'message' => 'Food add-on deleted successfully!']);
    }
}
