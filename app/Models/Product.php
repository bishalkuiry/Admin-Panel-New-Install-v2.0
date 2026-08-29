<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;
use App\Traits\ScopeByModule;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes, Searchable, ScopeByModule;

    protected $fillable = [
        'module_id', 'module_type', 'store_id', 'name', 'slug', 'sku', 'vendor_sku', 'barcode',
        'short_description', 'description', 'nutritional_info',
        'price', 'compare_price', 'tax_rate', 'tax_class', 'hsn_code',
        'quantity', 'low_stock_threshold',
        'unit', 'weight', 'weight_unit', 'length', 'width', 'height', 'dimension_unit',
        'category_id', 'brand_id', 'brand',
        'is_active', 'is_featured', 'status', 'visibility',
        'is_veg', 'is_prescription_required', 'generic_name', 'custom_attributes',
        'prep_time_min', 'prep_time_max', 'prep_time_unit', 'available_time_starts', 'available_time_ends',
        'is_halal', 'nutrition_info', 'search_tags', 'food_variations',
        'video_type', 'video_url', 'return_period_days', 'replacement_period_days',
        'warranty_summary', 'guarantee_summary', 'delivered_by_lead_hours',
        'track_inventory', 'allow_backorder',
        'manufacture_date', 'expiry_date', 'shelf_life_days',
        'meta_title', 'meta_description', 'meta_image',
        'approved_by', 'approved_at', 'rejection_reason', 'parent_product_id', 'commission'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'nutritional_info' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'status' => ProductStatus::class,
        'visibility' => ProductVisibility::class,
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'commission' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        static::updated(function ($product) {
            if ($product->wasChanged('quantity')) {
                $originalQuantity = (int) $product->getOriginal('quantity');
                $currentQuantity = (int) $product->quantity;

                if ($originalQuantity <= 0 && $currentQuantity > 0) {
                    static::dispatchRestockNotifications($product);
                }
            }
        });

        static::creating(function ($product) {
            if (empty($product->barcode)) {
                $product->barcode = self::generateBarcode();
            }
            if (empty($product->status)) {
                $product->status = ProductStatus::DRAFT;
            }

            // Auto-fill SEO fields if empty
            if (empty($product->meta_title)) {
                $product->meta_title = $product->name;
            }
            if (empty($product->meta_description) && !empty($product->short_description)) {
                $product->meta_description = Str::limit(strip_tags($product->short_description), 160);
            }
        });

        // Auto-fill meta_image from primary image after product is saved
        static::saved(function ($product) {
            // Only update meta_image if it's empty and product has a primary image
            if (empty($product->meta_image)) {
                $primaryImage = $product->primaryImage()->first();
                if ($primaryImage && $primaryImage->image) {
                    // Store the full URL for SEO/Open Graph
                    $imageUrl = storage_url($primaryImage->image);
                    $product->updateQuietly(['meta_image' => $imageUrl]);
                }
            }
        });
    }

    /**
     * Generate EAN-13 barcode
     */
    public static function generateBarcode(): string
    {
        // Country code (3) + Company code (4) + Product code (5) + Check digit (1)
        $prefix = '890'; // India prefix
        $random = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        $code = $prefix . $random;

        // Calculate check digit for EAN-13
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        return $code . $checkDigit;
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brandRelation(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProductAttachment::class)->orderBy('sort_order');
    }

    public function publicAttachments(): HasMany
    {
        return $this->attachments()->where('is_public', true);
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductStatus::PUBLISHED);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', ProductStatus::PENDING_APPROVAL);
    }

    public function scopeGloballyVisible($query)
    {
        return $query->where('visibility', ProductVisibility::GLOBAL);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeNearExpiry($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now());
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->where('store_id', $storeId)
              ->orWhere('visibility', ProductVisibility::GLOBAL);
        });
    }

    // Methods
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return 0;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->lte(now()->addDays($days));
    }

    public function needsApproval(): bool
    {
        return $this->status === ProductStatus::PENDING_APPROVAL;
    }

    public function isPublished(): bool
    {
        return $this->status === ProductStatus::PUBLISHED;
    }

    /**
     * Duplicate product
     */
    public function duplicate(?int $storeId = null): Product
    {
        $newProduct = $this->replicate([
            'slug', 'sku', 'barcode', 'approved_by', 'approved_at'
        ]);

        $newProduct->name = $this->name . ' (Copy)';
        $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(4);
        $newProduct->sku = 'PRD-' . strtoupper(Str::random(8));
        $newProduct->status = ProductStatus::DRAFT;
        $newProduct->parent_product_id = $this->id;

        if ($storeId) {
            $newProduct->store_id = $storeId;
        }

        $newProduct->save();

        // Copy images
        foreach ($this->images as $image) {
            $newProduct->images()->create($image->only(['image', 'alt_text', 'sort_order', 'is_primary']));
        }

        // Copy variants
        foreach ($this->variants as $variant) {
            $newVariant = $newProduct->variants()->create(
                $variant->only([
                    'name', 'mrp', 'selling_price', 'tax_rate',
                    'unit_id', 'unit_value', 'quantity', 'low_stock_threshold',
                    'weight', 'weight_unit', 'is_active', 'is_default', 'sort_order'
                ])
            );
            $newVariant->attributeValues()->sync($variant->attributeValues->pluck('id'));
        }

        // Copy tags
        $newProduct->tags()->sync($this->tags->pluck('id'));

        return $newProduct;
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'vendor_sku' => $this->vendor_sku,
            'short_description' => $this->short_description,
            'description' => strip_tags($this->description ?? ''),
            'brand' => $this->brand ?? $this->brandRelation?->name,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'status' => $this->status?->value,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category_id' => $this->category_id,
            'store_id' => $this->store_id,
            'tags' => $this->tags->pluck('name')->toArray(),
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::PUBLISHED && $this->is_active;
    }

    public function foodAddons()
    {
        return $this->belongsToMany(FoodAddon::class, 'food_addon_product', 'product_id', 'food_addon_id');
    }

    public function foodVariationGroups()
    {
        return $this->hasMany(FoodVariationGroup::class)->orderBy('sort_order');
    }

    public static function dispatchRestockNotifications(Product $product)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('stock_notifications')) return;

        $subscribers = \Illuminate\Support\Facades\DB::table('stock_notifications')
            ->where('product_id', $product->id)
            ->where('notified', false)
            ->get();

        foreach ($subscribers as $sub) {
            if ($sub->user_id) {
                $user = \App\Models\User::find($sub->user_id);
                if ($user) {
                    if ($user->fcm_token) {
                        \App\Helpers\NotificationHelper::sendFcmNotification(
                            $user->fcm_token,
                            '🎉 Back in Stock!',
                            "{$product->name} is now back in stock! Order before it sells out again.",
                            [
                                'type' => 'product_restocked',
                                'product_id' => (string) $product->id,
                            ]
                        );
                    }

                    if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                        \Illuminate\Support\Facades\DB::table('notifications')->insert([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'App\\Notifications\\ProductRestocked',
                            'notifiable_type' => 'App\\Models\\User',
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'title' => '🎉 Back in Stock!',
                                'message' => "{$product->name} is now back in stock! Order before it sells out again.",
                                'product_id' => $product->id,
                                'type' => 'product_restocked',
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            \Illuminate\Support\Facades\DB::table('stock_notifications')
                ->where('id', $sub->id)
                ->update(['notified' => true, 'updated_at' => now()]);
        }
    }
}
