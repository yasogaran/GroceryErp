<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'performed_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product that owns the stock movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed the movement.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope a query to only include stock-in movements.
     */
    public function scopeStockIn($query)
    {
        return $query->where('movement_type', 'in');
    }

    /**
     * Scope a query to only include stock-out movements.
     */
    public function scopeStockOut($query)
    {
        return $query->where('movement_type', 'out');
    }

    /**
     * Scope a query to only include adjustments.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('movement_type', 'adjustment');
    }

    /**
     * Scope a query to filter by reference type.
     */
    public function scopeByReference($query, string $type, ?int $id = null)
    {
        $query->where('reference_type', $type);

        if ($id !== null) {
            $query->where('reference_id', $id);
        }

        return $query;
    }

    /**
     * Scope a query to filter by expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    /**
     * Check if the stock is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the stock is expiring soon (within 30 days).
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date &&
               $this->expiry_date->isFuture() &&
               $this->expiry_date->lte(now()->addDays($days));
    }
}
