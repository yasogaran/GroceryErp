<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'batch_number',
        'expiry_date',
        'reference_type',
        'reference_id',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product for this stock movement
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this stock movement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
