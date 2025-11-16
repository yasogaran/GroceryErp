<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Calculate points for a sale
     * CUSTOMIZE THIS PER SHOP
     */
    public function calculatePoints(float $saleAmount): float
    {
        // Default: 1 point per Rs. 100 spent
        // Shop can customize this logic
        return floor($saleAmount / 100);
    }

    /**
     * Award points to customer for a sale
     */
    public function awardPoints(Customer $customer, Sale $sale): void
    {
        $points = $this->calculatePoints($sale->total_amount);

        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($customer, $sale, $points) {
            // Update customer balance
            $customer->increment('points_balance', $points);

            // Create transaction record
            PointTransaction::create([
                'customer_id' => $customer->id,
                'transaction_type' => 'earned',
                'points' => $points,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'created_by' => auth()->id(),
            ]);

            // Update sale record
            $sale->update(['points_earned' => $points]);
        });
    }

    /**
     * Redeem points (future enhancement)
     */
    public function redeemPoints(Customer $customer, float $points, string $reason): void
    {
        if ($customer->points_balance < $points) {
            throw new \Exception('Insufficient points balance');
        }

        DB::transaction(function () use ($customer, $points, $reason) {
            $customer->decrement('points_balance', $points);

            PointTransaction::create([
                'customer_id' => $customer->id,
                'transaction_type' => 'redeemed',
                'points' => -$points,
                'reference_type' => 'manual',
                'notes' => $reason,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Get customer points history
     */
    public function getPointsHistory(Customer $customer, int $limit = 20)
    {
        return PointTransaction::where('customer_id', $customer->id)
            ->with(['creator'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get points summary for customer
     */
    public function getPointsSummary(Customer $customer): array
    {
        $totalEarned = PointTransaction::where('customer_id', $customer->id)
            ->where('transaction_type', 'earned')
            ->sum('points');

        $totalRedeemed = PointTransaction::where('customer_id', $customer->id)
            ->where('transaction_type', 'redeemed')
            ->sum('points');

        return [
            'balance' => $customer->points_balance,
            'total_earned' => $totalEarned,
            'total_redeemed' => abs($totalRedeemed),
            'total_expired' => 0, // For future implementation
        ];
    }
}
