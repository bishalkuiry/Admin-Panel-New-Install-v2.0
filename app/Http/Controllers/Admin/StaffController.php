<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminStaff;
use App\Models\User;
use App\Models\Role;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class StaffController extends Controller
{
    private function excludedRoles(): array
    {
        return [
            UserRole::CUSTOMER->value,
            UserRole::STORE_OWNER->value,
            UserRole::STORE_MANAGER->value,
            UserRole::STORE_STAFF->value,
            UserRole::DELIVERY_PARTNER->value,
        ];
    }

    private function systemRoleSlugs(): array
    {
        return UserRole::systemSlugs();
    }

    public function index(Request $request)
    {
        if (Schema::hasTable('admin_staff') && AdminStaff::count() > 0) {
            $query = AdminStaff::query();
            if ($request->query('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            $users = $query->latest()->paginate(20);
        } else {
            $query = User::whereNotIn('role', $this->excludedRoles());
            if ($request->query('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            $users = $query->latest()->paginate(20);
        }

        return view('admin.staff.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereNotIn('slug', $this->systemRoleSlugs())->orderBy('name')->get();
        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:admin_staff,email',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => ['required'],
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if (Schema::hasTable('admin_staff')) {
            AdminStaff::create($validated);
        }
        User::create(array_merge($validated, ['role' => $validated['role']]));

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created successfully');
    }

    public function edit($id)
    {
        $staff = Schema::hasTable('admin_staff') && AdminStaff::find($id) 
            ? AdminStaff::findOrFail($id) 
            : User::findOrFail($id);

        $roles = Role::whereNotIn('slug', $this->systemRoleSlugs())->orderBy('name')->get();

        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $staff = Schema::hasTable('admin_staff') && AdminStaff::find($id) 
            ? AdminStaff::findOrFail($id) 
            : User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email'],
            'password'  => 'nullable|string|min:8|confirmed',
            'role'      => ['required'],
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $staff->update($validated);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated successfully');
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        if (Schema::hasTable('admin_staff') && AdminStaff::find($id)) {
            AdminStaff::destroy($id);
        }
        User::destroy($id);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted successfully.');
    }
}
