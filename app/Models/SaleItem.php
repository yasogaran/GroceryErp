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
        'quantity',
        'is_box_sale',
        'unit_price',
        'discount_amount',
        'total_price',
        'offer_id'
    ];

    protected $casts = [
        'is_box_sale' => 'boolean',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
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
}
