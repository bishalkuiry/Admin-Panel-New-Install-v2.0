<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\ScopeByModule;

class AppContent extends Model
{
    use ScopeByModule;

    protected $fillable = [
        'module_id',
        'header_tab_id',
        'type',
        'style',
        'title',
        'show_title',
        'subtitle',
        'show_subtitle',
        'source',
        'enable_background',
        'background_type',
        'background_color',
        'background_media_url',
        'grid_columns',
        'grid_rows',
        'enable_horizontal_animation',
        'show_on_category_screen',
        'show_on_tracking',
        'show_view_all',
        'media_items',
        'media_url',
        'media_type',
        'media_height',
        'media_width',
        'link_type',
        'link_id',
        'link_url',
        'custom_items',
        'sort_order',
        'is_active',
        'target',
    ];

    protected $casts = [
        'show_title' => 'boolean',
        'show_subtitle' => 'boolean',
        'show_view_all' => 'boolean',
        'is_active' => 'boolean',
        'enable_background' => 'boolean',
        'enable_horizontal_animation' => 'boolean',
        'show_on_category_screen' => 'boolean',
        'show_on_tracking' => 'boolean',
        'custom_items' => 'array',
        'media_items' => 'array',
        'media_height' => 'integer',
        'media_width' => 'integer',
        'grid_columns' => 'integer',
        'grid_rows' => 'integer',
    ];

    protected $touches = ['headerTab'];

    public function headerTab(): BelongsTo
    {
        return $this->belongsTo(HomeHeaderTab::class, 'header_tab_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeForTab($query, ?int $tabId)
    {
        if ($tabId === null) {
            return $query->whereNull('header_tab_id');
        }
        return $query->where(function ($q) use ($tabId) {
            $q->where('header_tab_id', $tabId)
              ->orWhereNull('header_tab_id');
        });
    }

    public function scopeForApp($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('target', ['app', 'all'])
              ->orWhereNull('target');
        });
    }

    /**
     * Get products based on source type
     */
    public function getProducts(int $limit = 10)
    {
        if ($this->type !== 'product') {
            return collect();
        }

        $query = Product::select([
            'id', 'store_id', 'category_id', 'brand_id', 'name', 'slug', 'sku',
            'price', 'compare_price', 'quantity', 'is_active', 'is_featured', 'created_at'
        ])
        ->where('is_active', true)
        ->with('primaryImage:id,product_id,image');

        switch ($this->source) {
            case 'custom':
                if (!empty($this->custom_items)) {
                    return $query->whereIn('id', $this->custom_items)
                        ->orderByRaw('FIELD(id, ' . implode(',', $this->custom_items) . ')')
                        ->limit($limit)
                        ->get();
                }
                return collect();

            case 'recent':
                return $query->latest()->limit($limit)->get();

            case 'featured':
                return $query->where('is_featured', true)->limit($limit)->get();

            default:
                return collect();
        }
    }

    /**
     * Get categories based on source type
     */
    public function getCategories(int $limit = 10)
    {
        if ($this->type !== 'category') {
            return collect();
        }

        $query = Category::select([
            'id', 'name', 'slug', 'image', 'icon', 'parent_id', 'is_active', 'is_featured', 'sort_order'
        ])->where('is_active', true);

        switch ($this->source) {
            case 'custom':
                if (!empty($this->custom_items)) {
                    return $query->whereIn('id', $this->custom_items)
                        ->orderByRaw('FIELD(id, ' . implode(',', $this->custom_items) . ')')
                        ->limit($limit)
                        ->get();
                }
                return collect();

            case 'featured':
                return $query->where('is_featured', true)->limit($limit)->get();

            default:
                return $query->whereNull('parent_id')->limit($limit)->get();
        }
    }

    /**
     * Get brands based on source type
     */
    public function getBrands(int $limit = 10)
    {
        if ($this->type !== 'brand') {
            return collect();
        }

        $query = Brand::select([
            'id', 'name', 'slug', 'logo', 'is_active', 'is_featured', 'sort_order'
        ])->where('is_active', true);

        switch ($this->source) {
            case 'custom':
                if (!empty($this->custom_items)) {
                    return $query->whereIn('id', $this->custom_items)
                        ->orderByRaw('FIELD(id, ' . implode(',', $this->custom_items) . ')')
                        ->limit($limit)
                        ->get();
                }
                return collect();

            case 'featured':
                return $query->where('is_featured', true)->limit($limit)->get();

            case 'recent':
                return $query->latest()->limit($limit)->get();

            default:
                return $query->orderBy('sort_order')->limit($limit)->get();
        }
    }
    /**
     * Get stores based on source type
     */
    public function getStores(int $limit = 10)
    {
        if ($this->type !== 'store') {
            return collect();
        }

        $query = Store::select([
            'id', 'name', 'slug', 'logo', 'banner', 'address_line_1 as address', 'rating', 'status', 'is_online', 'is_featured', 'order_count'
        ])
        ->where('status', \App\Enums\StoreStatus::ACTIVE);

        switch ($this->source) {
            case 'custom':
                if (!empty($this->custom_items)) {
                    return $query->whereIn('id', $this->custom_items)
                        ->orderByRaw('FIELD(id, ' . implode(',', $this->custom_items) . ')')
                        ->limit($limit)
                        ->get();
                }
                return collect();

            case 'featured':
                return $query->where('is_featured', true)->limit($limit)->get();

            case 'recent':
                return $query->latest()->limit($limit)->get();

            default:
                return $query->orderBy('order_count', 'desc')->limit($limit)->get(); // Default to most popular
        }
    }
}
