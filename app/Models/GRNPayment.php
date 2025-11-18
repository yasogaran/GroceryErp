<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GRNPayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'grn_id',
        'supplier_payment_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the GRN that this payment is for.
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class);
    }

    /**
     * Get the supplier payment that this allocation belongs to.
     */
    public function supplierPayment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class);
    }
}
