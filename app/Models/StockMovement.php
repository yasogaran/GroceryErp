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
     * The table associated with the model.
     */
    protected $table = 'stock_movements';

    /**
     * Indicates if the model should have updated_at timestamp.
     */
    public $timestamps = false;

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
        'unit_cost',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product that this stock movement belongs to.
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
  /*
     * Get the user who created this stock movement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
  /*
     * Get the user who performed this stock movement.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope a query to only include movements by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope a query to only include movements for a specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include movements with a specific reference.
     */
    public function scopeByReference($query, string $referenceType, int $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                     ->where('reference_id', $referenceId);
    }

    /**
     * Get a formatted movement type label.
     */
    public function getMovementTypeLabel(): string
    {
        return match($this->movement_type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'damage' => 'Damaged',
            'return' => 'Return',
            default => ucfirst($this->movement_type),
        };
    }

    /**
     * Get the movement direction (increase or decrease).
     */
    public function getDirection(): string
    {
        return $this->quantity >= 0 ? 'increase' : 'decrease';
    }
}
