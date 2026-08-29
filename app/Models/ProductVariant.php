<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'barcode', 'name',
        'mrp', 'selling_price', 'tax_rate',
        'unit_id', 'unit_value',
        'quantity', 'low_stock_threshold',
        'weight', 'weight_unit',
        'length', 'width', 'height',
        'is_active', 'is_default', 'image', 'sort_order'
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($variant) {
            // Auto-generate SKU if not provided
            if (empty($variant->sku)) {
                $product = Product::find($variant->product_id);
                $prefix = ($product->sku ?? 'VAR') . '-';
                $i = 1;
                while (self::withTrashed()->where('sku', $prefix . str_pad($i, 3, '0', STR_PAD_LEFT))->exists()) {
                    $i++;
                }
                $variant->sku = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT);
            }
            
            // Auto-generate barcode (EAN-13 format)
            if (empty($variant->barcode)) {
                $variant->barcode = self::generateBarcode();
            }
        });
    }

    /**
     * Generate EAN-13 barcode
     */
    public static function generateBarcode(): string
    {
        // Country code (2) + Company code (5) + Product code (5) + Check digit (1)
        $prefix = '890'; // India prefix
        $random = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        $code = $prefix . $random;
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes')
            ->withTimestamps();
    }

    /**
     * Get variant display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name && trim($this->name) !== '') {
            return $this->name;
        }

        $parts = [];
        if ($this->relationLoaded('attributeValues') && $this->attributeValues->isNotEmpty()) {
            foreach ($this->attributeValues as $value) {
                $parts[] = $value->value;
            }
        }

        if ($this->unit && $this->unit_value) {
            $parts[] = $this->unit_value . ($this->unit->short_name ?? $this->unit->name ?? '');
        } elseif ($this->unit_value) {
            $parts[] = $this->unit_value;
        }

        if (empty($parts) && $this->sku) {
            $skuParts = explode('-', $this->sku);
            if (count($skuParts) > 2) {
                $parts = array_slice($skuParts, 2);
            } else {
                $parts = [$this->sku];
            }
        }

        return !empty($parts) ? implode(' / ', $parts) : 'Default';
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentAttribute(): float
    {
        if ($this->mrp > $this->selling_price) {
            return round((($this->mrp - $this->selling_price) / $this->mrp) * 100, 1);
        }
        return 0;
    }

    /**
     * Check if low stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }
}
