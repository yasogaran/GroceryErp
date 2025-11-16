<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'return_id',
        'sale_item_id',
        'product_id',
        'returned_quantity',
        'refund_amount',
        'is_damaged',
        'notes',
    ];

    protected $casts = [
        'is_damaged' => 'boolean',
        'returned_quantity' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Get the return for this item
     */
    public function return()
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
    }

    /**
     * Get the original sale item
     */
    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * Get the product for this return item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
