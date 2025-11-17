<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the journal entry that owns this line.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account for this line.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Check if this is a debit entry.
     */
    public function isDebit(): bool
    {
        return bccomp($this->debit, '0', 2) > 0;
    }

    /**
     * Check if this is a credit entry.
     */
    public function isCredit(): bool
    {
        return bccomp($this->credit, '0', 2) > 0;
    }

    /**
     * Get the amount (debit or credit).
     */
    public function getAmount(): string
    {
        return $this->isDebit() ? $this->debit : $this->credit;
    }

    /**
     * Get the type (DR or CR).
     */
    public function getType(): string
    {
        return $this->isDebit() ? 'DR' : 'CR';
    }
}
