<?php

namespace App\Enums;

enum KycStatus: string
{
    case NOT_SUBMITTED = 'not_submitted';
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RESUBMIT_REQUIRED = 'resubmit_required';

    public function label(): string
    {
        return match($this) {
            self::NOT_SUBMITTED => 'Not Submitted',
            self::PENDING => 'Pending Review',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::RESUBMIT_REQUIRED => 'Resubmit Required',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NOT_SUBMITTED => 'gray',
            self::PENDING => 'yellow',
            self::UNDER_REVIEW => 'blue',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::RESUBMIT_REQUIRED => 'orange',
        };
    }
}
