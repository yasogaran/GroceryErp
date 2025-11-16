<?php

namespace App\Models;

use App\Services\InventoryService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class GRN extends Model
{
    use HasFactory, LogsActivity;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grn_number',
        'supplier_id',
        'grn_date',
        'total_amount',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'grn_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grns';

    /**
     * Get the supplier that owns the GRN.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for the GRN.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GRNItem::class, 'grn_id');
    }

    /**
     * Get the user who created the GRN.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the GRN.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate a unique GRN number.
     *
     * @return string
     */
    public static function generateGRNNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "GRN-{$date}-";

        // Get the last GRN number for today
        $lastGRN = self::where('grn_number', 'like', "{$prefix}%")
            ->orderBy('grn_number', 'desc')
            ->first();

        if ($lastGRN) {
            $lastNumber = (int) substr($lastGRN->grn_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Approve the GRN and update stock.
     *
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function approve(User $user): void
    {
        if ($this->status === self::STATUS_APPROVED) {
            throw new \Exception('GRN is already approved');
        }

        DB::transaction(function () use ($user) {
            // 1. Update GRN status
            $this->update([
                'status' => self::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // 2. Increase stock for each item
            $inventoryService = app(InventoryService::class);

            foreach ($this->items as $item) {
                $product = $item->product;

                $inventoryService->addStock($product, $item->received_pieces, [
                    'reference_type' => 'grn',
                    'reference_id' => $this->id,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date,
                    'manufacturing_date' => $item->manufacturing_date,
                    'notes' => "Stock received via {$this->grn_number}",
                ]);
            }

            // 3. Update supplier outstanding balance
            $this->supplier->updateOutstanding($this->total_amount, 'add');
        });
    }

    /**
     * Check if the GRN can be edited.
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the GRN can be deleted.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Scope a query to only include draft GRNs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope a query to only include approved GRNs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Calculate and update total amount.
     *
     * @return void
     */
    public function calculateTotal(): void
    {
        $total = $this->items()->sum('total_amount');
        $this->update(['total_amount' => $total]);
    }
}
