<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'type', 'file_path', 'file_type', 'file_size', 'is_public', 'sort_order'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    const TYPES = [
        'spec_sheet' => 'Specification Sheet',
        'fssai_certificate' => 'FSSAI Certificate',
        'iso_certificate' => 'ISO Certificate',
        'manual' => 'User Manual',
        'datasheet' => 'Technical Datasheet',
        'warranty' => 'Warranty Card',
        'other' => 'Other Document',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
