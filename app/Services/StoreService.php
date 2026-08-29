<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreStaff;
use App\Models\StoreKycDocument;
use App\Models\StorePayout;
use App\Models\User;
use App\Models\Zone;
use App\Models\Setting;
use App\Enums\StoreStatus;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Enums\VendorMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreService
{
    public function __construct(
        private RealtimeService $realtimeService
    ) {}

    /**
     * Check if multi-vendor mode is enabled
     */
    public function isMultiVendor(): bool
    {
        $mode = Setting::get('vendor_mode', VendorMode::MULTI->value);
        return $mode !== VendorMode::SINGLE->value;
    }

    /**
     * Register new store (merchant onboarding)
     */
    public function register(array $data, User $owner): Store
    {
        if (!$this->isMultiVendor()) {
            throw new \Exception('Seller registration is disabled. Single vendor mode is active.');
        }

        return DB::transaction(function () use ($data, $owner) {
            // Update user role to store owner
            $owner->update(['role' => UserRole::STORE_OWNER]);

            // Create store
            $store = Store::create([
                'owner_id' => $owner->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'email' => $data['email'] ?? $owner->email,
                'phone' => $data['phone'] ?? $owner->phone,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'],
                'business_type' => $data['business_type'] ?? null,
                'status' => StoreStatus::PENDING,
                'kyc_status' => KycStatus::NOT_SUBMITTED,
            ]);

            // Log activity
            $store->logActivity('store_registered', $owner, [
                'notes' => 'New store registration submitted',
            ]);

            // Notify admin
            $this->realtimeService->adminNotification(
                'New Store Registration',
                "Store '{$store->name}' has registered and is pending approval.",
                'info'
            );

            return $store;
        });
    }

    /**
     * Approve store
     */
    public function approve(Store $store, User $admin, ?string $notes = null): Store
    {
        return DB::transaction(function () use ($store, $admin, $notes) {
            $oldStatus = $store->status;

            $store->update([
                'status' => StoreStatus::ACTIVE,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);

            $store->logActivity('store_approved', $admin, [
                'old_values' => ['status' => $oldStatus->value],
                'new_values' => ['status' => StoreStatus::ACTIVE->value],
                'notes' => $notes,
            ]);

            // Notify store owner
            $this->realtimeService->notifyUser($store->owner_id, 'store-approved', [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'message' => 'Your store has been approved!',
            ]);

            return $store->fresh();
        });
    }

    /**
     * Reject store
     */
    public function reject(Store $store, User $admin, string $reason): Store
    {
        return DB::transaction(function () use ($store, $admin, $reason) {
            $oldStatus = $store->status;

            $store->update([
                'status' => StoreStatus::REJECTED,
                'rejection_reason' => $reason,
                'admin_notes' => $reason,
            ]);

            $store->logActivity('store_rejected', $admin, [
                'old_values' => ['status' => $oldStatus->value],
                'new_values' => ['status' => StoreStatus::REJECTED->value],
                'notes' => $reason,
            ]);

            // Notify store owner
            $this->realtimeService->notifyUser($store->owner_id, 'store-rejected', [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'reason' => $reason,
            ]);

            return $store->fresh();
        });
    }

    /**
     * Suspend store
     */
    public function suspend(Store $store, User $admin, string $reason): Store
    {
        return DB::transaction(function () use ($store, $admin, $reason) {
            $oldStatus = $store->status;

            $store->update([
                'status' => StoreStatus::SUSPENDED,
                'is_online' => false,
                'admin_notes' => $reason,
            ]);

            $store->logActivity('store_suspended', $admin, [
                'old_values' => ['status' => $oldStatus->value],
                'new_values' => ['status' => StoreStatus::SUSPENDED->value],
                'notes' => $reason,
            ]);

            return $store->fresh();
        });
    }

    /**
     * Assign zones to store
     */
    public function assignZones(Store $store, array $zoneIds, User $admin): Store
    {
        $store->zones()->sync($zoneIds);

        $store->logActivity('zones_assigned', $admin, [
            'new_values' => ['zone_ids' => $zoneIds],
            'notes' => 'Delivery zones updated',
        ]);

        return $store->fresh(['zones']);
    }

    /**
     * Add staff to store
     */
    public function addStaff(Store $store, array $data, User $addedBy): StoreStaff
    {
        return DB::transaction(function () use ($store, $data, $addedBy) {
            // Create user if not exists
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make($data['password'] ?? Str::random(12)),
                    'role' => UserRole::tryFrom($data['role']) ?? UserRole::STORE_STAFF,
                ]);
            } else {
                $user->update(['role' => UserRole::tryFrom($data['role']) ?? UserRole::STORE_STAFF]);
            }

            // Create staff record
            $staff = StoreStaff::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'role' => $data['role'],
                'permissions' => $data['permissions'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'designation' => $data['designation'] ?? null,
                'salary' => $data['salary'] ?? null,
                'joined_at' => $data['joined_at'] ?? now(),
            ]);

            $store->logActivity('staff_added', $addedBy, [
                'entity_type' => 'staff',
                'entity_id' => $staff->id,
                'new_values' => ['user_id' => $user->id, 'role' => $data['role']],
            ]);

            return $staff->load('user');
        });
    }

    /**
     * Remove staff from store
     */
    public function removeStaff(Store $store, StoreStaff $staff, User $removedBy): void
    {
        $store->logActivity('staff_removed', $removedBy, [
            'entity_type' => 'staff',
            'entity_id' => $staff->id,
            'old_values' => ['user_id' => $staff->user_id, 'role' => $staff->role],
        ]);

        // Reset user role to customer
        $staff->user->update(['role' => UserRole::CUSTOMER]);
        $staff->delete();
    }

    /**
     * Submit KYC document
     */
    public function submitKycDocument(Store $store, array $data): StoreKycDocument
    {
        $document = $store->kycDocuments()->updateOrCreate(
            ['document_type' => $data['document_type']],
            [
                'document_number' => $data['document_number'] ?? null,
                'file_path' => $data['file_path'],
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        // Update store KYC status
        $store->update(['kyc_status' => KycStatus::PENDING]);

        $store->logActivity('kyc_submitted', null, [
            'entity_type' => 'kyc_document',
            'entity_id' => $document->id,
            'new_values' => ['document_type' => $data['document_type']],
        ]);

        return $document;
    }

    /**
     * Verify KYC document
     */
    public function verifyKycDocument(StoreKycDocument $document, User $admin, bool $approved, ?string $reason = null): StoreKycDocument
    {
        $document->update([
            'status' => $approved ? 'approved' : 'rejected',
            'rejection_reason' => $approved ? null : $reason,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        // Check if all documents are approved
        $store = $document->store;
        $allApproved = $store->kycDocuments()->where('status', '!=', 'approved')->doesntExist();
        $anyRejected = $store->kycDocuments()->where('status', 'rejected')->exists();

        if ($allApproved) {
            $store->update(['kyc_status' => KycStatus::APPROVED]);
        } elseif ($anyRejected) {
            $store->update(['kyc_status' => KycStatus::RESUBMIT_REQUIRED]);
        }

        return $document->fresh();
    }

    /**
     * Create payout for store
     */
    public function createPayout(Store $store, float $amount, User $admin, array $data = []): StorePayout
    {
        // Payout amount must not exceed available balance
        if ($amount > $store->payout_balance) {
            throw new \Exception('Insufficient balance for payout.');
        }

        // TDS/Tax Deduction (Optional setting based on your region, 1% is common in India)
        $tax = Setting::get('tds_enabled', '0') === '1' ? ($amount * 0.01) : 0;
        $netAmount = $amount - $tax;

        return DB::transaction(function() use ($store, $amount, $tax, $netAmount, $admin, $data) {
            $payout = StorePayout::create([
                'store_id' => $store->id,
                'amount' => $amount,
                'commission_deducted' => 0, // Commission was already deducted at order level
                'tax_deducted' => $tax,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'period_start' => $data['period_start'] ?? null,
                'period_end' => $data['period_end'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Immediately decrement from balance to "freeze" funds
            $store->decrement('payout_balance', $amount);

            $store->logActivity('payout_created', $admin, [
                'entity_type' => 'payout',
                'entity_id' => $payout->id,
                'new_values' => ['requested_amount' => $amount, 'net_payable' => $netAmount],
            ]);

            return $payout;
        });
    }

    /**
     * Process payout
     */
    public function processPayout(StorePayout $payout, User $admin, string $transactionId): StorePayout
    {
        $payout->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        // Note: payout_balance was already decremented at creation time to freeze funds.

        $payout->store->logActivity('payout_processed', $admin, [
            'entity_type' => 'payout',
            'entity_id' => $payout->id,
            'new_values' => ['transaction_id' => $transactionId],
        ]);

        return $payout->fresh();
    }

    /**
     * Update store settings
     */
    public function updateSettings(Store $store, array $data, User $user): Store
    {
        $oldValues = $store->only(array_keys($data));

        $store->update($data);

        $store->logActivity('settings_updated', $user, [
            'old_values' => $oldValues,
            'new_values' => $data,
        ]);

        return $store->fresh();
    }

    /**
     * Toggle store online status
     */
    public function toggleOnline(Store $store, User $user): Store
    {
        $store->update(['is_online' => !$store->is_online]);

        $store->logActivity($store->is_online ? 'store_online' : 'store_offline', $user);

        return $store->fresh();
    }

    /**
     * Get stores with filters
     */
    public function getStores(array $filters = [], int $perPage = 15)
    {
        $query = Store::with(['owner', 'zones'])->withCount(['products', 'orders']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['kyc_status'])) {
            $query->where('kyc_status', $filters['kyc_status']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['zone_id'])) {
            $query->inZone($filters['zone_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('store_id', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_online'])) {
            $query->where('is_online', $filters['is_online']);
        }

        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get pending approvals
     */
    public function getPendingApprovals(int $perPage = 15)
    {
        return Store::with(['owner', 'kycDocuments'])
            ->where(function($q) {
                $q->whereIn('status', [StoreStatus::PENDING, StoreStatus::UNDER_REVIEW])
                  ->orWhereIn('kyc_status', [KycStatus::PENDING, KycStatus::UNDER_REVIEW])
                  ->orWhereHas('owner', function($uq) {
                      $uq->where('is_active', false)->orWhere('kyc_status', 'pending');
                  });
            })
            ->latest()
            ->paginate($perPage);
    }
}
