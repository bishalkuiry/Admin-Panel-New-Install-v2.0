<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Popup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'media_url',
        'media_type',
        'status',
        'position',
        'show_close_button',
        'click_action',
        'click_action_target',
        'display_trigger',
        'trigger_value',
        'audience_type',
        'zone_ids',
        'country_ids',
        'language_codes',
        'store_ids',
        'category_ids',
        'product_ids',
        'priority',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'show_close_button' => 'boolean',
        'zone_ids' => 'array',
        'country_ids' => 'array',
        'language_codes' => 'array',
        'store_ids' => 'array',
        'category_ids' => 'array',
        'product_ids' => 'array',
        'priority' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function getFormattedMediaUrlAttribute(): string
    {
        if (empty($this->media_url)) {
            return '';
        }
        if (str_starts_with($this->media_url, 'http://') || str_starts_with($this->media_url, 'https://')) {
            return $this->media_url;
        }
        return asset('storage/' . ltrim($this->media_url, '/'));
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->whereIn('status', ['active', 'scheduled'])
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });
    }
}
