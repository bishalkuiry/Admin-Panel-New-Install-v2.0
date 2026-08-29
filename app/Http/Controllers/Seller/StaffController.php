<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\StoreStaff;
use App\Models\User;
use App\Services\StoreService;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function __construct(
        private StoreService $storeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $store = $request->get('current_store');
        $staffMembers = $store->staff()->with('user')->latest()->paginate(20);

        return view('seller.staff.index', compact('store', 'staffMembers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $store = $request->get('current_store');
        return view('seller.staff.create', compact('store'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $store = $request->get('current_store');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:store_manager,store_staff',
            'designation' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'permissions' => 'nullable|array',
        ]);

        $this->storeService->addStaff($store, $validated, $request->user());

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, StoreStaff $staff)
    {
        $store = $request->get('current_store');
        
        // Ensure staff belongs to this store
        if ($staff->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('seller.staff.edit', compact('store', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreStaff $staff)
    {
        $store = $request->get('current_store');
        
        // Ensure staff belongs to this store
        if ($staff->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->user_id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:store_manager,store_staff',
            'designation' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Update user data
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? [],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $staff->user->update($userData);

        // Update staff record
        $staff->update([
            'role' => $validated['role'],
            'designation' => $validated['designation'],
            'employee_id' => $validated['employee_id'],
            'salary' => $validated['salary'],
            'permissions' => $validated['permissions'] ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, StoreStaff $staff)
    {
        $store = $request->get('current_store');
        
        // Ensure staff belongs to this store
        if ($staff->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        $this->storeService->removeStaff($store, $staff, $request->user());

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }

    /**
     * Toggle staff status
     */
    public function toggleStatus(Request $request, StoreStaff $staff)
    {
        $store = $request->get('current_store');
        
        // Ensure staff belongs to this store
        if ($staff->store_id !== $store->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $staff->is_active
        ]);
    }
}
