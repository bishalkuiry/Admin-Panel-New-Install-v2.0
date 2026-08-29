<?php

namespace App\Enums;

enum ProductVisibility: string
{
    case GLOBAL = 'global';           // Visible everywhere
    case STORE_ONLY = 'store_only';   // Only in specific store
    case HIDDEN = 'hidden';           // Not visible anywhere

    public function label(): string
    {
        return match($this) {
            self::GLOBAL => 'Global (All Stores)',
            self::STORE_ONLY => 'Store Specific',
            self::HIDDEN => 'Hidden',
        };
    }
}
