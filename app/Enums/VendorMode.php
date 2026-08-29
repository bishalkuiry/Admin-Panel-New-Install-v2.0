<?php

namespace App\Enums;

enum VendorMode: string
{
    case SINGLE = 'single';
    case MULTI = 'multi';

    public function label(): string
    {
        return match($this) {
            self::SINGLE => 'Single Vendor (Own Store Only)',
            self::MULTI => 'Multi Vendor (Marketplace)',
        };
    }

    public function allowsSellerRegistration(): bool
    {
        return $this === self::MULTI;
    }
}
