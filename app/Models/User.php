<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Support\DynamicRole;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'fcm_token',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'referral_code',
        'payout_balance',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_account_holder',
        'active_store_id',
        'notification_settings',
        'kyc_status',
        'kyc_rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'role' => UserRole::class, // Removed to allow dynamic roles
            'permissions' => 'array',
            'notification_settings' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'payout_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the user's role as a UserRole Enum (system roles) or DynamicRole (DB roles).
     *
     * Casting via 'role' => UserRole::class was removed because Laravel's built-in
     * Enum casting calls UserRole::from() which throws a ValueError for any slug
     * that is not defined in the Enum — i.e. any custom/dynamic role stored in the
     * roles table. This accessor uses tryFrom() (never throws) and falls back to a
     * DB lookup before giving up with a clear exception.
     *
     * @throws \UnexpectedValueException if the slug exists in neither the Enum nor DB.
     */
    public function getRoleAttribute($value): UserRole|DynamicRole
    {
        // Handle null/empty role by returning default CUSTOMER role
        if ($value === null || $value === '') {
            return UserRole::CUSTOMER;
        }

        // Already resolved (e.g. set during model hydration)
        if ($value instanceof UserRole) {
            return $value;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new \UnexpectedValueException(
                "User #{$this->id}: unexpected role type " . gettype($value)
            );
        }

        // Match a built-in system role
        $enumRole = UserRole::tryFrom((string) $value);
        if ($enumRole !== null) {
            return $enumRole;
        }

        // Match a custom DB role
        $roleModel = Role::where('slug', $value)->first();
        if ($roleModel) {
            return new DynamicRole($roleModel);
        }

        // No match — fail loudly so stale/deleted role slugs are caught immediately
        throw new \UnexpectedValueException(
            "User #{$this->id} has unknown role slug \"{$value}\". " .
            "The role may have been deleted. Please reassign the user's role."
        );
    }

    /**
     * Get user's addresses
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get user's default address
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /**
     * Get user's orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get orders assigned to this user as delivery partner
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_partner_id');
    }

    /**
     * Get user's cart
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get owned stores (for store owners)
     */
    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    /**
     * Get owned store (HasOne relationship)
     */
    public function ownedStore(): HasOne
    {
        return $this->hasOne(Store::class, 'owner_id');
    }

    /**
     * Get owned store (legacy support - returns first store)
     */
    public function getOwnedStoreAttribute()
    {
        return $this->ownedStores()->first();
    }

    /**
     * Get stores where user is staff
     */
    public function staffStores(): HasMany
    {
        return $this->hasMany(StoreStaff::class);
    }

    /**
     * Get active store
     */
    public function activeStore(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Store::class, 'active_store_id');
    }

    /**
     * Get user's wishlist items
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get user's reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get user's wallet
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get user's points balance
     */
    public function points(): HasOne
    {
        return $this->hasOne(UserPoints::class);
    }

    /**
     * Get user's payouts (for delivery partners)
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(DeliveryPartnerPayout::class);
    }

    /**
     * Get referrals made by this user (as referrer)
     */
    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get referral received by this user (as referee)
     */
    public function referralReceived(): HasOne
    {
        return $this->hasOne(Referral::class, 'referee_id');
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        // Check custom permissions first
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        // Check role-based permissions (Enum or Database)
        if ($this->role instanceof UserRole) {
            return in_array($permission, $this->role->permissions());
        }

        if ($this->role instanceof DynamicRole) {
            return in_array($permission, $this->role->permissions());
        }

        return false;
    }

    /**
     * Check if user is the primary system admin (oldest Super Admin)
     */
    public function isSystemAdmin(): bool
    {
        // 1. Must be a super admin
        $isSuperAdmin = false;
        if ($this->role instanceof UserRole) {
            $isSuperAdmin = $this->role === UserRole::SUPER_ADMIN;
        } elseif ($this->role instanceof DynamicRole) {
            $isSuperAdmin = $this->role->value === UserRole::SUPER_ADMIN->value;
        } else {
            $isSuperAdmin = $this->role === UserRole::SUPER_ADMIN->value;
        }

        if (!$isSuperAdmin) {
            return false;
        }

        // 2. Identify the first Super Admin ever created
        static $primaryAdminId = null;
        if ($primaryAdminId === null) {
            $primaryAdminId = \Illuminate\Support\Facades\Cache::remember('primary_system_admin_id', 86400, function() {
                return self::where('role', UserRole::SUPER_ADMIN->value)->orderBy('id')->value('id');
            });
        }

        return $this->id == $primaryAdminId;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        $role = $this->role;
        if ($role instanceof UserRole || $role instanceof DynamicRole) {
            return $role->isAdmin();
        }
        return in_array($role, [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }

    /**
     * Check if user has store role
     */
    public function isStoreRole(): bool
    {
        $role = $this->role;
        if ($role instanceof UserRole || $role instanceof DynamicRole) {
            return $role->isStoreRole();
        }
        return in_array($role, [UserRole::STORE_OWNER->value, UserRole::STORE_MANAGER->value, UserRole::STORE_STAFF->value]);
    }

    /**
     * Get default permissions for the user's role
     */
    public function getRolePermissions(): array
    {
        $role = $this->role;
        if ($role instanceof UserRole || $role instanceof DynamicRole) {
            return $role->permissions();
        }
        return [];
    }

    /**
     * Get current store for store users
     */
    public function getCurrentStore(): ?Store
    {
        // 1. Check persistent active store (API & Web)
        if ($this->active_store_id) {
            $store = $this->activeStore;
            // Verify access
            if ($store && ($store->owner_id === $this->id || $this->staffStores()->where('store_id', $store->id)->exists())) {
                return $store;
            }
        }

        // 2. For web requests, check session as fallback
        if (request()->hasSession() && session()->has('active_store_id')) {
            $store = Store::find(session('active_store_id'));
            if ($store && ($store->owner_id === $this->id || $this->staffStores()->where('store_id', $store->id)->exists())) {
                return $store;
            }
        }

        // 3. Default to first owned store
        if ($this->role === UserRole::STORE_OWNER) {
            return $this->ownedStores()->first();
        }

        // 4. Default to first staff store
        if ($this->isStoreRole()) {
            $staff = $this->staffStores()->where('is_active', true)->first();
            return $staff?->store;
        }

        return null;
    }
}
