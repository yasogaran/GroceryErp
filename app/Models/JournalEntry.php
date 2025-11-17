<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'entry_type',
        'status',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'created_by',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * Get the journal entry lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Get the user who created the entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who posted the entry.
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get the user who reversed the entry.
     */
    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Check if entry is balanced (debits = credits).
     */
    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 2) === 0;
    }

    /**
     * Check if entry can be posted.
     */
    public function canBePosted(): bool
    {
        return $this->status === 'draft'
            && $this->isBalanced()
            && $this->lines()->count() >= 2;
    }

    /**
     * Check if entry can be reversed.
     */
    public function canBeReversed(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if entry can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if entry can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Scope a query to only include posted entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope a query to only include draft entries.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include reversed entries.
     */
    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    /**
     * Scope a query by entry type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('entry_type', $type);
    }

    /**
     * Scope a query by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'draft' => 'bg-yellow-100 text-yellow-800',
            'posted' => 'bg-green-100 text-green-800',
            'reversed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get entry type badge class for UI.
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->entry_type) {
            'manual' => 'bg-blue-100 text-blue-800',
            'sale' => 'bg-green-100 text-green-800',
            'purchase' => 'bg-orange-100 text-orange-800',
            'payment' => 'bg-purple-100 text-purple-800',
            'return' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Generate the next entry number.
     */
    public static function generateEntryNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'JE-' . $today . '-';

        $lastEntry = static::where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
