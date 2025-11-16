<?php

namespace App\Services;

use App\Models\Product;

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
}
