<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeliveryPartnerController extends Controller
{
    public function __construct(private StorageService $storage) {}
    /**
     * List all delivery partners
     */
    public function index(Request $request)
    {
        $query = User::where('role', UserRole::DELIVERY_PARTNER)
            ->withCount(['deliveries as deliveries_count']);

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
            $status = $request->query('status');
            if ($status === 'pending') {
                $query->where('is_active', false)->where('kyc_status', '!=', 'rejected');
            } else if ($status === 'rejected') {
                $query->where('kyc_status', 'rejected');
            } else if ($status === 'active') {
                $query->where('is_active', true);
            } else if ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $partners = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total_partners' => User::where('role', UserRole::DELIVERY_PARTNER)->count(),
            'active_partners' => User::where('role', UserRole::DELIVERY_PARTNER)->where('is_active', true)->count(),
            'pending_partners' => User::where('role', UserRole::DELIVERY_PARTNER)->where('is_active', false)->where('kyc_status', '!=', 'rejected')->count(),
            'rejected_partners' => User::where('role', UserRole::DELIVERY_PARTNER)->where('kyc_status', 'rejected')->count(),
            'inactive_partners' => User::where('role', UserRole::DELIVERY_PARTNER)->where('is_active', false)->count(),
        ];

        return view('admin.delivery-partners.index', compact('partners', 'stats'));
    }

    /**
     * Show delivery partner details
     */
    public function show(User $deliveryPartner)
    {
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $stats = [
            'total_deliveries' => $deliveryPartner->deliveries()->count(),
            'completed_deliveries' => $deliveryPartner->deliveries()->where('status', OrderStatus::DELIVERED)->count(),
            'pending_deliveries' => $deliveryPartner->deliveries()->whereIn('status', [OrderStatus::PACKED, OrderStatus::PICKED_UP, OrderStatus::OUT_FOR_DELIVERY])->count(),
            'cancelled_deliveries' => $deliveryPartner->deliveries()->where('status', OrderStatus::CANCELLED)->count(),
        ];

        return view('admin.delivery-partners.show', compact('deliveryPartner', 'stats'));
    }

    /**
     * Show delivery partner deliveries
     */
    public function deliveries(User $deliveryPartner)
    {
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $deliveries = $deliveryPartner->deliveries()
            ->with(['store', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.delivery-partners.deliveries', compact('deliveryPartner', 'deliveries'));
    }

    /**
     * Show delivery partner payouts
     */
    public function payouts(User $deliveryPartner)
    {
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $payouts = $deliveryPartner->payouts()
            ->latest()
            ->paginate(20);

        return view('admin.delivery-partners.payouts', compact('deliveryPartner', 'payouts'));
    }

    /**
     * Process delivery partner payout
     */
    public function processPayout(Request $request, User $deliveryPartner)
    {
        // Ensure user is a delivery partner
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $deliveryPartner->payout_balance,
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $deliveryPartner) {
            $payout = $deliveryPartner->payouts()->create([
                'amount' => $request->input('amount'),
                'status' => 'completed',
                'payment_method' => $request->input('payment_method'),
                'transaction_id' => 'DP-' . strtoupper(uniqid()),
                'notes' => $request->input('notes'),
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $deliveryPartner->decrement('payout_balance', $request->input('amount'));
        });

        return back()->with('success', 'Payout processed successfully');
    }

    /**
     * Show create delivery partner form
     */
    public function create()
    {
        return view('admin.delivery-partners.create');
    }

    /**
     * Store new delivery partner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->storage->store($request->file('avatar'), 'avatars');
            }

            // Create delivery partner user
            $partner = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'avatar' => $avatarPath,
                'role' => UserRole::DELIVERY_PARTNER,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.delivery-partners.index')
                ->with('success', 'Delivery partner created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery partner: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(User $deliveryPartner)
    {
        // Ensure user is a delivery partner
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        return view('admin.delivery-partners.edit', compact('deliveryPartner'));
    }

    /**
     * Update delivery partner
     */
    public function update(Request $request, User $deliveryPartner)
    {
        // Ensure user is a delivery partner
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $deliveryPartner->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $deliveryPartner->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_ifsc' => 'nullable|string|max:255',
            'bank_account_holder' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'is_active' => $request->boolean('is_active'),
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_ifsc' => $validated['bank_ifsc'],
                'bank_account_holder' => $validated['bank_account_holder'],
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($deliveryPartner->avatar) {
                    $this->storage->delete($deliveryPartner->avatar);
                }
                $data['avatar'] = $this->storage->store($request->file('avatar'), 'avatars');
            }

            $deliveryPartner->update($data);

            DB::commit();

            return redirect()
                ->route('admin.delivery-partners.show', $deliveryPartner)
                ->with('success', 'Delivery partner updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update delivery partner: ' . $e->getMessage());
        }
    }

    /**
     * Delete delivery partner
     */
    public function destroy(User $deliveryPartner)
    {
        // Ensure user is a delivery partner
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        // Check if partner has active deliveries
        $activeDeliveries = $deliveryPartner->orders()
            ->whereIn('status', [
                OrderStatus::CONFIRMED,
                OrderStatus::PACKED,
                OrderStatus::PICKED_UP,
                OrderStatus::OUT_FOR_DELIVERY
            ])
            ->count();

        if ($activeDeliveries > 0) {
            return back()->with('error', 'Cannot delete delivery partner with active deliveries');
        }

        DB::beginTransaction();
        try {
            // Delete avatar if exists
            if ($deliveryPartner->avatar) {
                $this->storage->delete($deliveryPartner->avatar);
            }

            $deliveryPartner->delete();

            DB::commit();

            return redirect()
                ->route('admin.delivery-partners.index')
                ->with('success', 'Delivery partner deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete delivery partner: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(User $deliveryPartner)
    {
        // Ensure user is a delivery partner
        if ($deliveryPartner->role !== UserRole::DELIVERY_PARTNER) {
            abort(404, 'Delivery partner not found');
        }

        $newActive = !$deliveryPartner->is_active;
        $deliveryPartner->update([
            'is_active' => $newActive,
            'kyc_status' => $newActive ? 'approved' : 'rejected',
        ]);

        try {
            if ($newActive) {
                \Illuminate\Support\Facades\Mail::to($deliveryPartner->email)->send(new \App\Mail\AccountApprovedMail($deliveryPartner, 'Delivery Partner'));
            } else {
                \Illuminate\Support\Facades\Mail::to($deliveryPartner->email)->send(new \App\Mail\AccountRejectedMail($deliveryPartner, 'Account suspended by admin', 'Delivery Partner'));
            }
        } catch (\Exception $e) {}

        return back()->with('success', 'Status updated successfully and email notification sent');
    }

    /**
     * Approve driver account
     */
    public function approve(User $deliveryPartner)
    {
        $deliveryPartner->update([
            'is_active' => true,
            'kyc_status' => 'approved',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($deliveryPartner->email)->send(new \App\Mail\AccountApprovedMail($deliveryPartner, 'Delivery Partner'));
        } catch (\Exception $e) {}

        return back()->with('success', 'Driver account approved successfully! Confirmation email sent.');
    }

    /**
     * Reject driver account
     */
    public function reject(Request $request, User $deliveryPartner)
    {
        $reason = $request->input('reason', 'Verification documents did not meet criteria.');
        $deliveryPartner->update([
            'is_active' => false,
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => $reason,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($deliveryPartner->email)->send(new \App\Mail\AccountRejectedMail($deliveryPartner, $reason, 'Delivery Partner'));
        } catch (\Exception $e) {}

        return back()->with('success', 'Driver account rejected. Notification email sent.');
    }
}
