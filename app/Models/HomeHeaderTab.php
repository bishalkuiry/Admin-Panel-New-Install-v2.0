<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeHeaderTab extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'icon_url',
        'module_type',
        'use_header_name',
        'background_type',
        'background_url',
        'sticky_header_color',
        'header_bg_type',
        'header_gradient_color1',
        'header_gradient_color2',
        'header_gradient_style',
        'header_bg_image_url',
        'cards_horizontal',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'use_header_name' => 'boolean',
        'cards_horizontal' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(HomeHeaderCard::class, 'tab_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function allCards(): HasMany
    {
        return $this->hasMany(HomeHeaderCard::class, 'tab_id')->orderBy('sort_order');
    }

    /**
     * Get display name (custom name or category name)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }
        return $this->category?->name ?? 'All';
    }

    /**
     * Scope for active tabs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
