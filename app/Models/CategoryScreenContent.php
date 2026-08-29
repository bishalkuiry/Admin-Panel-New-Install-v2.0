<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryScreenContent extends Model
{
    protected $fillable = [
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
    ];

    protected $casts = [
        'show_title' => 'boolean',
        'show_subtitle' => 'boolean',
        'is_active' => 'boolean',
        'enable_background' => 'boolean',
        'enable_horizontal_animation' => 'boolean',
        'custom_items' => 'array',
        'media_items' => 'array',
        'media_height' => 'integer',
        'media_width' => 'integer',
        'grid_columns' => 'integer',
        'grid_rows' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get categories based on source type
     */
    public function getCategories(int $limit = 20)
    {
        if ($this->type !== 'category') {
            return collect();
        }

        $query = Category::where('is_active', true);

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

            case 'all':
            default:
                return $query->whereNull('parent_id')->limit($limit)->get();
        }
    }
}
