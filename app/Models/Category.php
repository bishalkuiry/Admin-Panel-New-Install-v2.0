<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use App\Traits\ScopeByModule;

class Category extends Model
{
    use HasFactory, SoftDeletes, Searchable, ScopeByModule;

    protected $fillable = [
        'module_id', 'name', 'slug', 'image', 'banner',
        'parent_id', 'sort_order', 'is_active', 'is_featured',
        'commission_percent'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'commission_percent' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Touch updated_at to ensure ETag changes
        // No need to manage cache keys since we query fresh timestamps
        static::saved(function ($category) {
            // The updated_at timestamp change is enough for ETag invalidation
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($ancestors, $parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get breadcrumb path
     */
    public function getBreadcrumbAttribute(): array
    {
        $path = $this->ancestors();
        $path[] = $this;
        
        return array_map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
        ], $path);
    }

    /**
     * Get depth level
     */
    public function getDepthAttribute(): int
    {
        return count($this->ancestors());
    }

    /**
     * Get all descendant IDs (for filtering)
     */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        
        return $ids;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get products including from child categories
     */
    public function allProducts()
    {
        $categoryIds = $this->getAllDescendantIds();
        return Product::whereIn('category_id', $categoryIds);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeParentCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Get flat tree for select dropdown
     */
    public static function getTreeForSelect(): array
    {
        $categories = self::with('children.children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $result = [];
        self::flattenTree($categories, $result);
        
        return $result;
    }

    private static function flattenTree($categories, &$result, $prefix = ''): void
    {
        foreach ($categories as $category) {
            $result[$category->id] = $prefix . $category->name;
            
            if ($category->children->isNotEmpty()) {
                self::flattenTree($category->children, $result, $prefix . '— ');
            }
        }
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => (bool) $this->is_active,
            'parent_id' => $this->parent_id ? (int) $this->parent_id : null,
            'sort_order' => (int) $this->sort_order,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
