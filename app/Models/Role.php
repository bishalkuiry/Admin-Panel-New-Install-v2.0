<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'permissions',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Check if role has system flag
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Get available permissions for this role
     */
    public function permissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Check if role is Admin/SuperAdmin
     */
    public function isAdmin(): bool
    {
        return in_array($this->slug, ['admin', 'super_admin']);
    }

    /**
     * Check if role is a Store role
     */
    public function isStoreRole(): bool
    {
        return in_array($this->slug, ['store_owner', 'store_manager', 'store_staff']);
    }
}
