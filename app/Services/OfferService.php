<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Collection;

class OfferService
{
    /**
     * Find best applicable offer for a cart item
     */
    public function findBestOffer(Product $product, float $quantity, float $basePrice): ?array
    {
        $applicableOffers = Offer::active()
            ->byPriority()
            ->get()
            ->filter(function($offer) use ($product) {
                return $offer->isApplicableToProduct($product);
            });

        if ($applicableOffers->isEmpty()) {
            return null;
        }

        // Calculate discount for each offer and pick best
        $bestOffer = null;
        $maxDiscount = 0;

        foreach ($applicableOffers as $offer) {
            $result = $offer->calculateDiscount($quantity, $basePrice);

            if ($result['discount_amount'] > $maxDiscount) {
                $maxDiscount = $result['discount_amount'];
                $bestOffer = [
                    'offer_id' => $offer->id,
                    'offer_name' => $offer->name,
                    'discount_amount' => $result['discount_amount'],
                    'description' => $result['offer_description'],
                    'free_items' => $result['free_items'] ?? 0,
                ];
            }
        }

        return $bestOffer;
    }

    /**
     * Apply offers to entire cart
     */
    public function applyOffersToCart(array $cartItems): array
    {
        foreach ($cartItems as &$item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $offer = $this->findBestOffer(
                $product,
                $item['quantity'],
                $item['quantity'] * $item['unit_price']
            );

            if ($offer) {
                $item['offer_id'] = $offer['offer_id'];
                $item['offer_discount'] = $offer['discount_amount'];
                $item['offer_description'] = $offer['description'];
            }
        }

        return $cartItems;
    }
}
