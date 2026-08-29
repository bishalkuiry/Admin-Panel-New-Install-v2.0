<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->getAvailablePermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Ensure slug is unique
        if (Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'A role with this name already exists.'])->withInput();
        }

        Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'permissions' => $request->permissions ?? [],
            'is_system' => false,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = $this->getAvailablePermissions();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        // Don't update slug for existing roles to avoid breaking links
        // Only update name and permissions
        
        $role->update([
            'name' => $validated['name'],
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Get all available permissions grouped by module
     */
    private function getAvailablePermissions(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'View Dashboard',
            ],
            'Products' => [
                'products.view' => 'View Products',
                'products.create' => 'Create Products',
                'products.update' => 'Edit Products',
                'products.delete' => 'Delete Products',
                'brands.view' => 'View Brands',
                'brands.create' => 'Create Brands',
                'brands.update' => 'Edit Brands',
                'brands.delete' => 'Delete Brands',
            ],
            'Attributes' => [
                'attributes.view' => 'View Attributes',
                'attributes.create' => 'Create Attributes',
                'attributes.update' => 'Edit Attributes',
                'attributes.delete' => 'Delete Attributes',
            ],
            'Orders' => [
                'orders.view' => 'View Orders',
                'orders.update' => 'Edit Orders',
                'orders.delete' => 'Delete Orders',
                'coupons.view' => 'View Coupons',
                'coupons.create' => 'Create Coupons',
                'coupons.update' => 'Edit Coupons',
                'coupons.delete' => 'Delete Coupons',
            ],
            'Categories' => [
                'categories.view' => 'View Categories',
                'categories.create' => 'Create Categories',
                'categories.update' => 'Edit Categories',
                'categories.delete' => 'Delete Categories',
            ],
            'Delivery Partners' => [
                'delivery_partners.view' => 'View Delivery Partners',
                'delivery_partners.create' => 'Create Delivery Partners',
                'delivery_partners.update' => 'Edit Delivery Partners',
                'delivery_partners.delete' => 'Delete Delivery Partners',
                'delivery_partners.payouts' => 'Manage Delivery Payouts',
            ],
            'Zones' => [
                'zones.view' => 'View Zones',
                'zones.create' => 'Create Zones',
                'zones.update' => 'Edit Zones',
                'zones.delete' => 'Delete Zones',
            ],
            'Users' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.update' => 'Edit Users',
                'users.delete' => 'Delete Users',
            ],
            'Staff' => [
                'staff.view' => 'View Staff',
                'staff.create' => 'Create Staff',
                'staff.update' => 'Edit Staff',
                'staff.delete' => 'Delete Staff',
            ],
            'Wallets' => [
                'wallets.view' => 'View Wallets',
                'wallets.manage' => 'Manage Wallets',
            ],
            'Stores' => [
                'stores.view' => 'View Stores',
                'stores.create' => 'Create Stores',
                'stores.update' => 'Edit Stores',
                'stores.delete' => 'Delete Stores',
                'stores.approve' => 'Approve/Reject Stores',
            ],
            'Sellers' => [
                'sellers.view' => 'View Sellers',
                'sellers.create' => 'Create Sellers',
                'sellers.update' => 'Edit Sellers',
                'sellers.delete' => 'Delete Sellers',
                'sellers.approve' => 'Approve/Reject Sellers',
            ],
            'Settings' => [
                'settings.view' => 'View Settings',
                'settings.manage' => 'Manage Settings',
                'settings.auth' => 'Manage Authentication Settings',
            ],
             'Roles' => [
                'roles.view' => 'View Roles',
                'roles.create' => 'Create Roles',
                'roles.update' => 'Edit Roles',
                'roles.delete' => 'Delete Roles',
            ],
            'Pages' => [
                'pages.view' => 'View Static Pages',
                'pages.create' => 'Create Static Pages',
                'pages.update' => 'Edit Static Pages',
                'pages.delete' => 'Delete Static Pages',
            ],
            'Reviews' => [
                'reviews.view' => 'View Reviews',
                'reviews.update' => 'Edit Reviews',
                'reviews.delete' => 'Delete Reviews',
            ],
            'Loyalty' => [
                'loyalty.view' => 'View Loyalty Analytics',
                'loyalty.manage' => 'Manage Loyalty Settings',
            ],
            'Reports' => [
                'reports.view' => 'View Reports',
                'reports.export' => 'Export Reports',
            ],
            'Media' => [
                'media.view' => 'View Media Library',
                'media.create' => 'Upload Media',
                'media.delete' => 'Delete Media',
            ],
            'Mobile App' => [
                'mobile_app.view' => 'View Mobile App Settings',
                'mobile_app.manage' => 'Manage Mobile App Settings',
            ],
            'Landing Page' => [
                'landing_page.view' => 'View Landing Page',
                'landing_page.manage' => 'Manage Landing Page',
            ],
            'System' => [
                'database_clean.view' => 'View Database Clean',
                'database_clean.perform' => 'Perform System Cleanup',
            ],
            'Email & SMTP' => [
                'email_templates.view' => 'View Email Templates',
                'email_templates.update' => 'Edit Email Templates',
                'settings.email' => 'Manage SMTP Settings',
            ],
        ];
    }
}
