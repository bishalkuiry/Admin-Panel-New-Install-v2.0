<?php

namespace App\Enums;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
    case REMOVED = 'removed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::PUBLISHED => 'Published',
            self::REJECTED => 'Rejected',
            self::REMOVED => 'Removed',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING_APPROVAL => 'yellow',
            self::PUBLISHED => 'green',
            self::REJECTED => 'red',
            self::REMOVED => 'orange',
            self::ARCHIVED => 'purple',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::PUBLISHED;
    }
}
