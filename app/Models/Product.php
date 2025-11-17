<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'brand',
        'base_unit',
        'min_selling_price',
        'max_selling_price',
        'current_stock_quantity',
        'damaged_stock_quantity',
        'reorder_level',
        'image_path',
        'is_active',
        'has_packaging',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:2',
        'box_price' => 'decimal:2',
        'current_stock_quantity' => 'decimal:2',
        'damaged_stock_quantity' => 'decimal:2',
        'minimum_stock_level' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'current_stock_quantity' => 'decimal:2',
        'damaged_stock_quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_active' => 'boolean',
        'has_packaging' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the packaging configuration for the product.
     */
    public function packaging(): HasOne
    {
        return $this->hasOne(ProductPackaging::class);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to search by name, SKU, or barcode.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * Check if the product has stock.
     */
    public function hasStock(): bool
    {
        return $this->current_stock_quantity > 0;
    }

    /**
     * Check if the product can be deleted.
     * A product cannot be deleted if it has stock.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasStock() && $this->damaged_stock_quantity == 0;
    }

    /**
     * Check if the product is below reorder level.
     */
    public function isBelowReorderLevel(): bool
    {
        return $this->current_stock_quantity <= $this->reorder_level;
    }

    /**
     * Generate a unique SKU.
     */
    public static function generateUniqueSku(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(substr(uniqid(), -8));
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Generate a unique barcode.
     */
    public static function generateUniqueBarcode(): string
    {
        do {
            // Generate a 13-digit EAN-13 barcode
            $barcode = '20' . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $barcode)->exists() || ProductPackaging::where('package_barcode', $barcode)->exists());

        return $barcode;
    }
}
