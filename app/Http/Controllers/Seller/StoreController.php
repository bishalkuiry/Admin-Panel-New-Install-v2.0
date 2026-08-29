<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function __construct(private StorageService $storage) {}
    /**
     * Show store settings
     */
    public function edit(Request $request)
    {
        $store = $request->current_store;
        return view('seller.store.settings', compact('store'));
    }

    /**
     * Update store settings
     */
    public function update(Request $request)
    {
        $store = $request->current_store;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'opening_hours' => 'nullable|array',
            'is_online' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_ifsc' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->storage->store($request->file('logo'), 'stores/logos');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $this->storage->store($request->file('banner'), 'stores/banners');
        }

        $validated['is_online'] = $request->boolean('is_online');
        
        // Map address to address_line_1
        if (isset($validated['address'])) {
            $validated['address_line_1'] = $validated['address'];
            unset($validated['address']);
        }

        $store->update($validated);

        return back()->with('success', 'Store settings updated successfully');
    }

    /**
     * Switch active store
     */
    public function switchStore(Request $request, Store $store)
    {
        $user = Auth::user();

        // Check ownership or staff access
        if ($store->owner_id !== $user->id && !$user->staffStores()->where('store_id', $store->id)->exists()) {
            abort(403);
        }

        session(['active_store_id' => $store->id]);

        return back()->with('success', "Switched to store: {$store->name}");
    }
}
