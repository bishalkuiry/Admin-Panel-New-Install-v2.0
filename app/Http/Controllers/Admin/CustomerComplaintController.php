<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplaint;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerComplaint::with(['user', 'order', 'store'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $complaints = $query->paginate(20);

        return view('admin.complaints.index', compact('complaints'));
    }

    public function show(CustomerComplaint $complaint)
    {
        $complaint->load(['user', 'order.items.product', 'store']);
        return view('admin.complaints.show', compact('complaint'));
    }

    public function resolve(Request $request, CustomerComplaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,resolved',
            'action_taken' => 'required|in:none,refund_customer,penalize_vendor,penalize_driver,suspend_account',
            'penalty_amount' => 'nullable|numeric|min:0',
            'admin_response' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $complaint->status = $validated['status'];
            $complaint->action_taken = $validated['action_taken'];
            if ($request->filled('penalty_amount')) {
                $complaint->penalty_amount = $validated['penalty_amount'];
            }
            if ($request->filled('admin_response')) {
                $complaint->admin_response = $validated['admin_response'];
            }
            $complaint->save();

            // Default Penalty from Settings if not specified
            $penalty = (float)($complaint->penalty_amount ?? 0);
            $defaultVendorPenalty = (float)(\App\Models\Setting::where('key', 'complaint_default_vendor_penalty')->value('value') ?? 500);
            if ($penalty <= 0 && in_array($validated['action_taken'], ['penalize_vendor', 'refund_customer'])) {
                $penalty = $defaultVendorPenalty;
                $complaint->penalty_amount = $penalty;
                $complaint->save();
            }

            // Action: Refund Customer (Credit User Wallet)
            if ($validated['action_taken'] === 'refund_customer' && $penalty > 0) {
                $userWallet = Wallet::firstOrCreate(['user_id' => $complaint->user_id], ['balance' => 0.00]);
                $balanceBefore = (float) $userWallet->balance;
                $userWallet->balance += $penalty;
                $userWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $userWallet->id,
                    'user_id' => $complaint->user_id,
                    'amount' => $penalty,
                    'type' => 'credit',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $userWallet->balance,
                    'description' => "Complaint Refund Ticket #{$complaint->ticket_number}",
                    'reference_id' => $complaint->id,
                    'reference_type' => CustomerComplaint::class,
                ]);
            }

            // Action: Penalize Vendor (Deduct Vendor Wallet)
            if ($validated['action_taken'] === 'penalize_vendor' && $complaint->store_id && $penalty > 0) {
                $store = $complaint->store;
                if ($store && $store->vendor_id) {
                    $vendorWallet = Wallet::firstOrCreate(['user_id' => $store->vendor_id], ['balance' => 0.00]);
                    $balanceBefore = (float) $vendorWallet->balance;
                    $vendorWallet->balance -= $penalty;
                    $vendorWallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $vendorWallet->id,
                        'user_id' => $store->vendor_id,
                        'amount' => $penalty,
                        'type' => 'debit',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $vendorWallet->balance,
                        'description' => "Penalty for Complaint Ticket #{$complaint->ticket_number}",
                        'reference_id' => $complaint->id,
                        'reference_type' => CustomerComplaint::class,
                    ]);
                }
            }

            // Automated Strike Checks & Auto-Suspension
            $vendorStrikeLimit = (int)(\App\Models\Setting::where('key', 'complaint_auto_suspend_vendor_strikes')->value('value') ?? 3);
            $driverStrikeLimit = (int)(\App\Models\Setting::where('key', 'complaint_auto_suspend_driver_strikes')->value('value') ?? 3);

            if ($complaint->store_id && $complaint->store?->vendor_id) {
                $vendorApprovedCount = CustomerComplaint::where('store_id', $complaint->store_id)->where('status', 'approved')->count();
                if ($vendorApprovedCount >= $vendorStrikeLimit || $validated['action_taken'] === 'suspend_account') {
                    User::where('id', $complaint->store->vendor_id)->update([
                        'is_active' => false,
                        'suspension_reason' => "Automated suspension: Reached {$vendorApprovedCount} approved complaints (Strike limit: {$vendorStrikeLimit}).",
                    ]);
                }
            }

            if ($complaint->driver_id) {
                $driverApprovedCount = CustomerComplaint::where('driver_id', $complaint->driver_id)->where('status', 'approved')->count();
                if ($driverApprovedCount >= $driverStrikeLimit) {
                    User::where('id', $complaint->driver_id)->update([
                        'is_active' => false,
                        'suspension_reason' => "Automated suspension: Reached {$driverApprovedCount} approved driver complaints (Strike limit: {$driverStrikeLimit}).",
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Complaint Ticket #{$complaint->ticket_number} resolved with action " . ucfirst(str_replace('_', ' ', $validated['action_taken'])));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to resolve complaint', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to resolve complaint: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        $settings = [
            'default_vendor_penalty' => \App\Models\Setting::where('key', 'complaint_default_vendor_penalty')->value('value') ?? '500',
            'auto_suspend_vendor_strikes' => \App\Models\Setting::where('key', 'complaint_auto_suspend_vendor_strikes')->value('value') ?? '3',
            'auto_suspend_driver_strikes' => \App\Models\Setting::where('key', 'complaint_auto_suspend_driver_strikes')->value('value') ?? '3',
            'admin_notification_email' => \App\Models\Setting::where('key', 'complaint_notification_email')->value('value') ?? 'support@inallcart.com',
            'allowed_categories' => \App\Models\Setting::where('key', 'complaint_categories')->value('value') ?? 'Food Quality,Wrong Item,Damaged Packaging,Late Delivery,Rider Behavior,Missing Item',
        ];

        return view('admin.complaints.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'default_vendor_penalty' => 'required|numeric|min:0',
            'auto_suspend_vendor_strikes' => 'required|integer|min:1',
            'auto_suspend_driver_strikes' => 'required|integer|min:1',
            'admin_notification_email' => 'required|email',
            'allowed_categories' => 'required|string',
        ]);

        foreach ($validated as $key => $val) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'complaint_' . $key],
                ['value' => (string)$val]
            );
        }

        return redirect()->back()->with('success', 'Complaint System Settings saved successfully!');
    }
}
