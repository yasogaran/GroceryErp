<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_id',
        'is_system_account',
        'balance',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system_account' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get all child accounts recursively.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include accounts by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope a query to only include system accounts.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system_account', true);
    }

    /**
     * Scope a query to only include custom (non-system) accounts.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system_account', false);
    }

    /**
     * Check if the account has any child accounts.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if the account can be deleted.
     * System accounts or accounts with transactions cannot be deleted.
     */
    public function canBeDeleted(): bool
    {
        // System accounts cannot be deleted
        if ($this->is_system_account) {
            return false;
        }

        // Check if has child accounts
        if ($this->hasChildren()) {
            return false;
        }

        // TODO: When transactions are implemented, also check:
        // if ($this->transactions()->count() > 0) {
        //     return false;
        // }

        return true;
    }

    /**
     * Check if the account can be edited.
     * System accounts cannot be edited (except balance updates via transactions).
     */
    public function canBeEdited(): bool
    {
        return !$this->is_system_account;
    }
}
