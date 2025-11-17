<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SaleReturn extends Model
{
    use LogsActivity;

    protected $fillable = [
        'return_number',
        'original_sale_id',
        'customer_id',
        'return_date',
        'total_refund_amount',
        'refund_mode',
        'bank_account_id',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'datetime',
        'total_refund_amount' => 'decimal:2',
    ];

    /**
     * Get the original sale for this return
     */
    public function originalSale()
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    /**
     * Get the customer for this return
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all items for this return
     */
    public function items()
    {
        return $this->hasMany(SaleReturnItem::class, 'return_id');
    }

    /**
     * Get the bank account for this return (if bank transfer)
     */
    public function bankAccount()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    /**
     * Get the user who created this return
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate a unique return number
     */
    public static function generateReturnNumber()
    {
        $prefix = 'RET-' . date('Ymd') . '-';
        $latest = self::where('return_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($latest->return_number, -4);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
