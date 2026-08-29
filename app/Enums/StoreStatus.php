<?php

namespace App\Enums;

enum StoreStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case REJECTED = 'rejected';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Approval',
            self::UNDER_REVIEW => 'Under Review',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::REJECTED => 'Rejected',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'bg-amber-100 text-amber-700 border border-amber-200',
            self::UNDER_REVIEW => 'bg-blue-100 text-blue-700 border border-blue-200',
            self::ACTIVE => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            self::SUSPENDED => 'bg-orange-100 text-orange-700 border border-orange-200',
            self::REJECTED => 'bg-rose-100 text-rose-700 border border-rose-200',
            self::CLOSED => 'bg-gray-100 text-gray-700 border border-gray-200',
        };
    }

    public function canAcceptOrders(): bool
    {
        return $this === self::ACTIVE;
    }
}
