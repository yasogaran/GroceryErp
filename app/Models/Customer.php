<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'email',
        'address',
        'points_balance',
        'total_purchases',
        'is_active'
    ];

    protected $casts = [
        'points_balance' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all sales for this customer
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all point transactions for this customer
     */
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Scope to get only active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted points balance
     */
    public function getFormattedPointsAttribute()
    {
        return number_format($this->points_balance, 2);
    }

    /**
     * Generate a unique customer code
     */
    public static function generateCustomerCode()
    {
        $latest = self::latest('id')->first();
        $number = $latest ? $latest->id + 1 : 1;
        return 'CUST-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
