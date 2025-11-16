<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Exception;

class ShiftService
{
    /**
     * Open a new shift for a cashier
     */
    public function openShift(User $cashier, float $openingCash): Shift
    {
        // Check if cashier already has an open shift
        if ($this->hasOpenShift($cashier)) {
            throw new Exception("You already have an open shift. Please close it first.");
        }

        return Shift::create([
            'cashier_id' => $cashier->id,
            'shift_start' => now(),
            'opening_cash' => $openingCash,
        ]);
    }

    /**
     * Close an existing shift
     */
    public function closeShift(Shift $shift, float $closingCash, ?string $notes = null): Shift
    {
        $expectedCash = $shift->opening_cash + $shift->total_cash_sales;
        $variance = $closingCash - $expectedCash;

        $shift->update([
            'shift_end' => now(),
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_variance' => $variance,
            'variance_notes' => $notes,
            'is_verified' => true,
        ]);

        return $shift;
    }

    /**
     * Check if a cashier has an open shift
     */
    public function hasOpenShift(User $cashier): bool
    {
        return Shift::where('cashier_id', $cashier->id)
            ->whereNull('shift_end')
            ->exists();
    }

    /**
     * Get the current open shift for a cashier
     */
    public function getCurrentShift(User $cashier): ?Shift
    {
        return Shift::where('cashier_id', $cashier->id)
            ->whereNull('shift_end')
            ->first();
    }
}
