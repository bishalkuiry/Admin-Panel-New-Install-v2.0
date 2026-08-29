<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreStaff extends Model
{
    use HasFactory;

    protected $table = 'store_staff';

    protected $fillable = [
        'store_id',
        'user_id',
        'role',
        'permissions',
        'employee_id',
        'designation',
        'salary',
        'joined_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'salary' => 'decimal:2',
        'joined_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if staff has permission
     */
    public function hasPermission(string $permission): bool
    {
        // Check custom permissions first
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        // Check role-based permissions
        $role = UserRole::tryFrom($this->role);
        return $role ? in_array($permission, $role->permissions()) : false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
