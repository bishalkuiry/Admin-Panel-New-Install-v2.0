<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case STORE_OWNER = 'store_owner';
    case STORE_MANAGER = 'store_manager';
    case STORE_STAFF = 'store_staff';
    case DELIVERY_PARTNER = 'delivery_partner';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::STORE_OWNER => 'Store Owner',
            self::STORE_MANAGER => 'Store Manager',
            self::STORE_STAFF => 'Store Staff',
            self::DELIVERY_PARTNER => 'Delivery Partner',
            self::CUSTOMER => 'Customer',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::SUPER_ADMIN => ['*'],
            self::ADMIN => [
                'dashboard.view',
                'stores.view', 'stores.create', 'stores.update', 'stores.delete', 'stores.approve',
                'orders.view', 'orders.update', 'orders.delete',
                'products.view', 'products.create', 'products.update', 'products.delete',
                'users.view', 'users.create', 'users.update', 'users.delete',
                'staff.view', 'staff.create', 'staff.update', 'staff.delete',
                'sellers.view', 'sellers.create', 'sellers.update', 'sellers.delete', 'sellers.approve',
                'delivery_partners.view', 'delivery_partners.create', 'delivery_partners.update', 'delivery_partners.delete', 'delivery_partners.payouts',
                'zones.view', 'zones.create', 'zones.update', 'zones.delete',
                'settings.view', 'settings.manage',
                'reports.view', 'reports.export',
                'loyalty.view', 'loyalty.manage',
                'reviews.view', 'reviews.update', 'reviews.delete',
                'media.view', 'media.create', 'media.delete',
                'mobile_app.view', 'mobile_app.manage',
                'landing_page.view', 'landing_page.manage',
                'database_clean.view', 'database_clean.perform',
                'brands.view', 'brands.create', 'brands.update', 'brands.delete',
                'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',
                'pages.view', 'pages.create', 'pages.update', 'pages.delete',
                'wallets.view', 'wallets.manage',
                'roles.view', 'roles.create', 'roles.update', 'roles.delete',
                'rides.view', 'rides.manage', 'drivers.view', 'drivers.manage', 'drivers.approve', 'ride-settings.view', 'ride-settings.update', 'ride-analytics.view', 'vehicle-types.manage', 'surge-pricing.manage',
                'website-builder.view', 'website-builder.manage',
            ],
            self::STORE_OWNER => [
                'store.view', 'store.edit',
                'store.products.view', 'store.products.manage',
                'store.orders.view', 'store.orders.manage',
                'store.staff.view', 'store.staff.manage',
                'store.reports.view', 'store.payouts.view',
            ],
            self::STORE_MANAGER => [
                'store.view',
                'store.products.view', 'store.products.manage',
                'store.orders.view', 'store.orders.manage',
                'store.staff.view',
                'store.reports.view',
            ],
            self::STORE_STAFF => [
                'store.view',
                'store.products.view',
                'store.orders.view', 'store.orders.update',
            ],
            self::DELIVERY_PARTNER => [
                'deliveries.view', 'deliveries.update',
            ],
            self::CUSTOMER => [
                'orders.own', 'profile.edit',
            ],
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    public function isStoreRole(): bool
    {
        return in_array($this, [self::STORE_OWNER, self::STORE_MANAGER, self::STORE_STAFF]);
    }

    /**
     * All built-in system role slugs.
     * Use this as the single source of truth instead of copy-pasting arrays.
     *
     * @return string[]
     */
    public static function systemSlugs(): array
    {
        return array_map(fn(self $role) => $role->value, self::cases());
    }
}
