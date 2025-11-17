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

    /**
     * Get the full account hierarchy path (e.g., "Assets > Current Assets > Cash")
     */
    public function getHierarchyPath(): string
    {
        $path = [$this->account_name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->account_name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get the account level in hierarchy (0 = root)
     */
    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Get suggested next account code for a given parent
     */
    public static function getNextAccountCode(?int $parentId = null): string
    {
        if ($parentId) {
            $parent = static::find($parentId);
            if ($parent) {
                // Get last child account code
                $lastChild = static::where('parent_id', $parentId)
                    ->orderBy('account_code', 'desc')
                    ->first();

                if ($lastChild) {
                    // Increment the code
                    $nextCode = (int)$lastChild->account_code + 10;
                    return (string)$nextCode;
                }

                // No children yet, return parent code + 10
                return (int)$parent->account_code + 10;
            }
        }

        // No parent - return next available root account code
        $lastRoot = static::whereNull('parent_id')
            ->orderBy('account_code', 'desc')
            ->first();

        if ($lastRoot) {
            return (string)((int)$lastRoot->account_code + 1000);
        }

        return '1000'; // Default starting code for assets
    }

    /**
     * Get account type color for UI
     */
    public function getTypeColor(): string
    {
        return match($this->account_type) {
            'asset' => 'green',
            'liability' => 'red',
            'equity' => 'purple',
            'income' => 'blue',
            'expense' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get account type badge classes for UI
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->account_type) {
            'asset' => 'bg-green-100 text-green-800',
            'liability' => 'bg-red-100 text-red-800',
            'equity' => 'bg-purple-100 text-purple-800',
            'income' => 'bg-blue-100 text-blue-800',
            'expense' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Scope to get only root (parent) accounts
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get accounts with their full tree
     */
    public function scopeWithTree($query)
    {
        return $query->with(['children.children.children']);
    }
}
