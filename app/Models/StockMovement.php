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
        'expiry_date',
        'notes',
        'performed_by',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Get the product that this stock movement belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
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
