<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use App\Traits\ScopeByModule;

class Brand extends Model
{
    use HasFactory, SoftDeletes, ScopeByModule;

    protected $fillable = [
        'module_id', 'name', 'slug', 'logo',
        'is_active', 'is_featured', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Stores that can sell this brand (admin assigns)
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_brands')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Active stores for this brand
     */
    public function activeStores(): BelongsToMany
    {
        return $this->stores()->wherePivot('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if store can sell this brand
     */
    public function canBeSoldBy(Store $store): bool
    {
        return $this->stores()->where('store_id', $store->id)->wherePivot('is_active', true)->exists();
    }
}
