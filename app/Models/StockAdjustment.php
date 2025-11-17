<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class StockAdjustment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'adjustment_type',
        'quantity',
        'unit_cost',
        'min_selling_price',
        'max_selling_price',
        'batch_number',
        'reason',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the product for this adjustment
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this adjustment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved/rejected this adjustment
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending adjustments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved adjustments
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected adjustments
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Approve the stock adjustment and apply to inventory
     */
    public function approve(User $user)
    {
        return DB::transaction(function() use ($user) {
            // Update adjustment status
            $this->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Apply stock adjustment using InventoryService
            $inventoryService = app(InventoryService::class);

            // Prepare details with pricing information
            $details = [
                'reference_type' => 'adjustment',
                'reference_id' => $this->id,
                'notes' => 'Adjustment approved: ' . $this->reason . ' - ' . ($this->notes ?? ''),
                'batch_number' => $this->batch_number,
            ];

            // Add pricing information if available
            if ($this->unit_cost !== null) {
                $details['unit_cost'] = $this->unit_cost;
            }
            if ($this->min_selling_price !== null) {
                $details['min_selling_price'] = $this->min_selling_price;
            }
            if ($this->max_selling_price !== null) {
                $details['max_selling_price'] = $this->max_selling_price;
            }

            if ($this->adjustment_type === 'increase') {
                $inventoryService->addStock($this->product, $this->quantity, $details);
            } else {
                $inventoryService->reduceStock($this->product, $this->quantity, $details);
            }

            return true;
        });
    }

    /**
     * Reject the stock adjustment
     */
    public function reject(User $user)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Check if adjustment can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if adjustment can be rejected
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }
}
