<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'stock_movement_id',
        'quantity',
        'is_box_sale',
        'unit_price',
        'unit_cost',
        'discount_amount',
        'total_price',
        'offer_id'
    ];

    protected $casts = [
        'is_box_sale' => 'boolean',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the sale that owns this item
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product for this sale item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock movement (batch) used for this sale item
     */
    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    /**
     * Calculate the gross profit for this item
     * Gross Profit = (Selling Price - Cost) * Quantity
     */
    public function getGrossProfitAttribute(): float
    {
        if ($this->unit_cost === null) {
            return 0;
        }
        return ($this->unit_price - $this->unit_cost) * $this->quantity;
    }

    /**
     * Calculate the profit margin percentage
     * Margin % = ((Selling Price - Cost) / Selling Price) * 100
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->unit_price <= 0 || $this->unit_cost === null) {
            return 0;
        }
        return (($this->unit_price - $this->unit_cost) / $this->unit_price) * 100;
    }
}
