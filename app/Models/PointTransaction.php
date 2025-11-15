<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'transaction_type',
        'points',
        'reference_type',
        'reference_id',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'points' => 'decimal:2',
    ];

    /**
     * Get the customer that owns this transaction
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
