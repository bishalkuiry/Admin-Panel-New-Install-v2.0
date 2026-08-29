<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Models\Setting;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    /**
     * List all sellers (store owners)
     */
    public function index(Request $request)
    {
        $query = User::where('role', UserRole::STORE_OWNER)
            ->with(['ownedStore', 'wallet'])
            ->withCount(['orders']);

        // Search
        if ($request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->query('status')) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        // Filter by store status
        if ($request->query('store_status')) {
            $storeStatus = $request->query('store_status');
            $query->whereHas('ownedStore', function ($q) use ($storeStatus) {
                $q->where('status', $storeStatus);
            });
        }

        $sellers = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total_sellers' => User::where('role', UserRole::STORE_OWNER)->count(),
            'active_sellers' => User::where('role', UserRole::STORE_OWNER)->where('is_active', true)->count(),
            'with_stores' => User::where('role', UserRole::STORE_OWNER)->has('ownedStore')->count(),
            'without_stores' => User::where('role', UserRole::STORE_OWNER)->doesntHave('ownedStore')->count(),
        ];

        return view('admin.sellers.index', compact('sellers', 'stats'));
    }

    /**
     * Show seller details
     */
    public function show(User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        $seller->load([
            'ownedStore.zones',
            'ownedStore.products',
            'ownedStore.orders' => fn($q) => $q->latest()->limit(10),
            'ownedStore.payouts' => fn($q) => $q->latest()->limit(10),
            'wallet.transactions' => fn($q) => $q->latest()->limit(10),
            'orders' => fn($q) => $q->latest()->limit(10),
        ]);

        // Get available stores (stores without owners)
        $availableStores = Store::whereNull('owner_id')->get();

        return view('admin.sellers.show', compact('seller', 'availableStores'));
    }

    /**
     * Show create seller form
     */
    public function create()
    {
        // Get stores without owners
        $availableStores = Store::whereNull('owner_id')->get();
        
        return view('admin.sellers.create', compact('availableStores'));
    }

    /**
     * Store new seller
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create seller user
            $seller = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::STORE_OWNER,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Create wallet for seller
            $seller->wallet()->create([
                'balance' => 0,
                'currency' => Setting::get('default_currency', 'INR'),
            ]);

            // Assign store if provided
            if ($request->filled('store_id')) {
                $store = Store::findOrFail($validated['store_id']);
                $store->update(['owner_id' => $seller->id]);
                $store->logActivity('owner_assigned', Auth::user(), [
                    'notes' => "Seller {$seller->name} assigned as owner"
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.sellers.show', $seller)
                ->with('success', 'Seller created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create seller: ' . $e->getMessage());
        }
    }

    /**
     * Update seller
     */
    public function update(Request $request, User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $seller->id,
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $seller->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $seller->update($validated);

        return back()->with('success', 'Seller updated successfully');
    }

    /**
     * Assign store to seller
     */
    public function assignStore(Request $request, User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        $store = Store::findOrFail($validated['store_id']);

        // Check if store already has an owner
        if ($store->owner_id && $store->owner_id !== $seller->id) {
            return back()->with('error', 'This store is already assigned to another seller');
        }

        // Check if seller already has a store
        if ($seller->ownedStore && $seller->ownedStore->id !== $store->id) {
            return back()->with('error', 'This seller already owns another store. Please unassign the current store first.');
        }

        $store->update(['owner_id' => $seller->id]);
        $store->logActivity('owner_assigned', Auth::user(), [
            'notes' => "Seller {$seller->name} assigned as owner"
        ]);

        return back()->with('success', 'Store assigned successfully');
    }

    /**
     * Unassign store from seller
     */
    public function unassignStore(User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        if (!$seller->ownedStore) {
            return back()->with('error', 'Seller does not have an assigned store');
        }

        $store = $seller->ownedStore;
        $store->update(['owner_id' => null]);
        $store->logActivity('owner_unassigned', Auth::user(), [
            'notes' => "Seller {$seller->name} unassigned from store"
        ]);

        return back()->with('success', 'Store unassigned successfully');
    }

    /**
     * Toggle seller active status
     */
    public function toggleStatus(User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        $seller->update(['is_active' => !$seller->is_active]);

        $status = $seller->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Seller {$status} successfully");
    }

    /**
     * Delete seller
     */
    public function destroy(User $seller)
    {
        // Ensure user is a seller
        if ($seller->role !== UserRole::STORE_OWNER) {
            abort(404, 'Seller not found');
        }

        // Check if seller has a store
        if ($seller->ownedStore) {
            return back()->with('error', 'Cannot delete seller with an assigned store. Please unassign the store first.');
        }

        // Check if seller has orders
        if ($seller->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete seller with existing orders');
        }

        $seller->delete();

        return redirect()
            ->route('admin.sellers.index')
            ->with('success', 'Seller deleted successfully');
    }
}
