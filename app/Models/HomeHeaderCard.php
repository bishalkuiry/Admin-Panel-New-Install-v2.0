<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class HomeHeaderCard extends Model
{
    protected $fillable = [
        'tab_id',
        'image_url',
        'link_type',
        'link_id',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Touch parent tab when card is modified
     */
    protected $touches = ['tab'];

    public function tab(): BelongsTo
    {
        return $this->belongsTo(HomeHeaderTab::class, 'tab_id');
    }

    /**
     * Get the linked entity based on link_type
     */
    public function getLinkedEntityAttribute()
    {
        return match ($this->link_type) {
            'category' => Category::find($this->link_id),
            'product' => Product::find($this->link_id),
            'store' => Store::find($this->link_id),
            default => null,
        };
    }

    /**
     * Scope for active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
