<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SupplierPayment extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'payment_date',
        'amount',
        'payment_mode',
        'bank_reference',
        'reference_number',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Validate that payment doesn't exceed outstanding balance
            $supplier = Supplier::find($payment->supplier_id);

            if ($payment->amount > $supplier->outstanding_balance) {
                throw new \Exception("Payment amount ({$payment->amount}) cannot exceed outstanding balance ({$supplier->outstanding_balance})");
            }
        });

        static::created(function ($payment) {
            // Update supplier outstanding balance
            DB::transaction(function () use ($payment) {
                $supplier = Supplier::find($payment->supplier_id);
                $supplier->updateOutstanding($payment->amount, 'subtract');
            });
        });
    }

    /**
     * Get the supplier that owns the payment.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created the payment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the GRN payment allocations for this payment.
     */
    public function grnPayments(): HasMany
    {
        return $this->hasMany(GRNPayment::class);
    }

    /**
     * Scope a query to filter by payment mode.
     */
    public function scopeByPaymentMode($query, string $mode)
    {
        return $query->where('payment_mode', $mode);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
}
