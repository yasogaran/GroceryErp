<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Sale extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'shift_id',
        'customer_id',
        'sale_date',
        'subtotal',
        'discount_amount',
        'discount_type',
        'total_amount',
        'payment_status',
        'status',
        'points_earned',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'points_earned' => 'decimal:2',
    ];

    /**
     * Get the shift that owns this sale
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the customer for this sale
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all items for this sale
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get all payments for this sale
     */
    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Get the cashier who created this sale
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate a unique invoice number
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $latest = self::where('invoice_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($latest->invoice_number, -4);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
