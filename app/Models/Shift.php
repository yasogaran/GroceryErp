<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Shift extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cashier_id',
        'shift_start',
        'shift_end',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_variance',
        'total_sales',
        'total_cash_sales',
        'total_bank_sales',
        'total_transactions',
        'is_verified',
        'variance_notes'
    ];

    protected $casts = [
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
        'is_verified' => 'boolean',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_variance' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_bank_sales' => 'decimal:2',
    ];

    /**
     * Get the cashier who owns this shift
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get all sales for this shift
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope to get only open shifts
     */
    public function scopeOpen($query)
    {
        return $query->whereNull('shift_end');
    }

    /**
     * Scope to get only closed shifts
     */
    public function scopeClosed($query)
    {
        return $query->whereNotNull('shift_end');
    }

    /**
     * Check if shift is open
     */
    public function getIsOpenAttribute()
    {
        return is_null($this->shift_end);
    }

    /**
     * Get shift duration
     */
    public function getDurationAttribute()
    {
        if (!$this->shift_end) {
            return null;
        }
        return $this->shift_start->diffForHumans($this->shift_end, true);
    }
}
