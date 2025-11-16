<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_mode',
        'bank_account_id',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the sale that owns this payment
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the bank account for this payment (if applicable)
     */
    public function bankAccount()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }
}
