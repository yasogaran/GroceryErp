<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;

class POSService
{
    /**
     * Calculate item price with box discount
     * Returns: ['unit_price', 'base_total', 'discount', 'final_total']
     */
    public function calculateItemPrice(Product $product, float $quantity, bool $isBoxSale): array
    {
        $unitPrice = $product->max_selling_price;
        $baseTotal = $quantity * $unitPrice;
        $discount = 0;

        // Apply box discount if applicable
        if ($isBoxSale && $product->has_packaging && $product->packaging) {
            $discount = $this->calculateBoxDiscount($product, $baseTotal);
        }

        return [
            'unit_price' => round($unitPrice, 2),
            'base_total' => round($baseTotal, 2),
            'discount' => round($discount, 2),
            'final_total' => round($baseTotal - $discount, 2)
        ];
    }

    /**
     * Calculate box discount
     */
    private function calculateBoxDiscount(Product $product, float $basePrice): float
    {
        $packaging = $product->packaging;

        if (!$packaging) {
            return 0;
        }

        if ($packaging->discount_type === 'percentage') {
            return ($basePrice * $packaging->discount_value) / 100;
        }

        return $packaging->discount_value;
    }

    /**
     * Validate price override (manual price entry)
     */
    public function validatePriceOverride(Product $product, float $newPrice): bool
    {
        return $newPrice >= $product->min_selling_price
            && $newPrice <= $product->max_selling_price;
    }

    /**
     * Check if product has sufficient stock
     */
    public function checkStock(Product $product, float $quantity): bool
    {
        return $product->current_stock_quantity >= $quantity;
    }

    /**
     * Apply item-level discount
     * Returns: ['unit_price', 'base_total', 'box_discount', 'item_discount', 'total_discount', 'final_total']
     */
    public function applyItemDiscount(
        Product $product,
        float $quantity,
        bool $isBoxSale,
        float $discountAmount = 0,
        string $discountType = 'fixed' // fixed or percentage
    ): array {
        // Base calculation (includes box discount if applicable)
        $pricing = $this->calculateItemPrice($product, $quantity, $isBoxSale);

        // Apply additional item discount
        $additionalDiscount = 0;
        if ($discountAmount > 0) {
            if ($discountType === 'percentage') {
                $additionalDiscount = ($pricing['final_total'] * $discountAmount) / 100;
            } else {
                $additionalDiscount = $discountAmount;
            }
        }

        return [
            'unit_price' => $pricing['unit_price'],
            'base_total' => $pricing['base_total'],
            'box_discount' => $pricing['discount'],
            'item_discount' => round($additionalDiscount, 2),
            'total_discount' => round($pricing['discount'] + $additionalDiscount, 2),
            'final_total' => round($pricing['final_total'] - $additionalDiscount, 2),
        ];
    }

    /**
     * Validate if user is authorized to apply discount
     * Returns true if user can apply the discount percentage
     */
    public function validateDiscountAuthorization(User $user, float $discountPercent): bool
    {
        $maxDiscount = match($user->role) {
            'cashier' => 5,      // 5% max
            'manager' => 20,     // 20% max
            'admin' => 100,      // Unlimited
            default => 0,
        };

        return $discountPercent <= $maxDiscount;
    }

    /**
     * Get maximum discount percentage for user role
     */
    public function getMaxDiscountForRole(string $role): float
    {
        return match($role) {
            'cashier' => 5,
            'manager' => 20,
            'admin' => 100,
            default => 0,
        };
    }
}
