<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Attribute extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name', 'slug', 'type', 'is_filterable', 'is_visible', 'sort_order'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'is_filterable' => $this->is_filterable,
            'is_visible' => $this->is_visible,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
