<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreKycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'document_type',
        'document_number',
        'file_path',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    const DOCUMENT_TYPES = [
        'pan_card' => 'PAN Card',
        'gst_certificate' => 'GST Certificate',
        'business_proof' => 'Business Registration Proof',
        'owner_id' => 'Owner ID (Aadhaar/Passport)',
        'fssai' => 'FSSAI License',
        'trade_license' => 'Trade License',
        'bank_proof' => 'Bank Account Proof',
        'address_proof' => 'Address Proof',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
