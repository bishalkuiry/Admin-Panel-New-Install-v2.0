<?php

namespace App\Support;

use App\Models\Role;
use App\Enums\UserRole;

class DynamicRole
{
    public string $value;
    public string $name;
    protected array $permissions;

    public function __construct(Role $role)
    {
        $this->value = $role->slug;
        $this->name = $role->name;
        $this->permissions = $role->permissions ?? [];
    }

    public function label(): string
    {
        return $this->name;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }

    public function isAdmin(): bool
    {
        return in_array($this->value, [UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }

    public function isStoreRole(): bool
    {
        return in_array($this->value, [
            UserRole::STORE_OWNER->value,
            UserRole::STORE_MANAGER->value,
            UserRole::STORE_STAFF->value
        ]);
    }

    /**
     * Magic method to allow access to value property like an Enum
     */
    public function __get($name)
    {
        if ($name === 'value') {
            return $this->value;
        }
        return null;
    }
}
