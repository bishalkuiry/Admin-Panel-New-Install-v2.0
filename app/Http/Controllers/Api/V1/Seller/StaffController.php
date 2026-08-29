<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Models\StoreStaff;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        private StoreService $storeService
    ) {}

    /**
     * List store staff
     */
    public function index(Request $request)
    {
        $store = $request->user()->getCurrentStore();
        $staff = $store->staff()->with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $staff,
        ]);
    }

    /**
     * Add staff member
     */
    public function store(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:store_manager,store_staff',
            'designation' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'permissions' => 'nullable|array',
        ]);

        $staff = $this->storeService->addStaff($store, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Staff member added successfully',
            'data' => $staff,
        ], 201);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, int $staffId)
    {
        $store = $request->user()->getCurrentStore();
        $staff = $store->staff()->findOrFail($staffId);

        $validated = $request->validate([
            'role' => 'sometimes|in:store_manager,store_staff',
            'designation' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'permissions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $staff->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Staff member updated',
            'data' => $staff->fresh('user'),
        ]);
    }

    /**
     * Remove staff member
     */
    public function destroy(Request $request, int $staffId)
    {
        $store = $request->user()->getCurrentStore();
        $staff = $store->staff()->findOrFail($staffId);

        $this->storeService->removeStaff($store, $staff, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Staff member removed',
        ]);
    }

    /**
     * Toggle staff active status
     */
    public function toggleActive(Request $request, int $staffId)
    {
        $store = $request->user()->getCurrentStore();
        $staff = $store->staff()->findOrFail($staffId);

        $staff->update(['is_active' => !$staff->is_active]);

        return response()->json([
            'success' => true,
            'message' => $staff->is_active ? 'Staff activated' : 'Staff deactivated',
            'data' => ['is_active' => $staff->is_active],
        ]);
    }
}
