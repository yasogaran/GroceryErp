<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'credit_terms',
        'outstanding_balance',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_terms' => 'integer',
        'outstanding_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the GRNs for the supplier.
     */
    public function grns(): HasMany
    {
        return $this->hasMany(GRN::class);
    }

    /**
     * Get the payments for the supplier.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Update the outstanding balance.
     *
     * @param float $amount
     * @param string $operation 'add' or 'subtract'
     * @return void
     */
    public function updateOutstanding(float $amount, string $operation = 'add'): void
    {
        if ($operation === 'add') {
            $this->increment('outstanding_balance', $amount);
        } elseif ($operation === 'subtract') {
            $this->decrement('outstanding_balance', $amount);
        }
    }

    /**
     * Check if the supplier can be deleted.
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->grns()->count() === 0 &&
               $this->outstanding_balance == 0;
    }

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter suppliers with outstanding balance.
     */
    public function scopeWithOutstanding($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    /**
     * Scope a query to search by name, contact, email, or phone.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Get the total purchases from this supplier.
     *
     * @return float
     */
    public function getTotalPurchases(): float
    {
        return $this->grns()
            ->where('status', 'approved')
            ->sum('total_amount');
    }

    /**
     * Get the total payments made to this supplier.
     *
     * @return float
     */
    public function getTotalPayments(): float
    {
        return $this->payments()->sum('amount');
    }
}
