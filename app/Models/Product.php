<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'description',
        'unit_price',
        'box_price',
        'pieces_per_box',
        'current_stock_quantity',
        'damaged_stock_quantity',
        'minimum_stock_level',
        'barcode',
        'is_active',
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
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock_quantity', '<=', 'minimum_stock_level');
    }

    /**
     * Check if the product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock_quantity <= $this->minimum_stock_level;
    }

    /**
     * Check if the product supports box pricing.
     */
    public function hasBoxPricing(): bool
    {
        return $this->box_price !== null && $this->pieces_per_box !== null;
    }

    /**
     * Get the total stock value (current stock * unit price).
     */
    public function getStockValue(): float
    {
        return (float) ($this->current_stock_quantity * $this->unit_price);
    }

    /**
     * Get the available stock (excluding damaged).
     */
    public function getAvailableStock(): float
    {
        return (float) $this->current_stock_quantity;
    }
}
