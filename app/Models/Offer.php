<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Offer extends Model
{
    use LogsActivity;

    const TYPE_BUY_X_GET_Y = 'buy_x_get_y';
    const TYPE_QUANTITY_DISCOUNT = 'quantity_discount';

    protected $fillable = [
        'name',
        'description',
        'offer_type',
        'start_date',
        'end_date',
        'is_active',
        'buy_quantity',
        'get_quantity',
        'min_quantity',
        'discount_type',
        'discount_value',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'offer_products');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    // Methods
    public function isApplicableToProduct(Product $product): bool
    {
        // Check if product is directly linked
        if ($this->products()->where('product_id', $product->id)->exists()) {
            return true;
        }

        // Check if product's category is linked
        if ($product->category_id && $this->categories()->where('category_id', $product->category_id)->exists()) {
            return true;
        }

        return false;
    }

    public function calculateDiscount(float $quantity, float $basePrice): array
    {
        if ($this->offer_type === self::TYPE_BUY_X_GET_Y) {
            return $this->calculateBuyXGetY($quantity, $basePrice);
        } else {
            return $this->calculateQuantityDiscount($quantity, $basePrice);
        }
    }

    private function calculateBuyXGetY(float $quantity, float $basePrice): array
    {
        // Example: Buy 2 Get 1 Free
        // If quantity = 5, customer gets floor(5/3) = 1 free item
        $setSize = $this->buy_quantity + $this->get_quantity;
        $completeSets = floor($quantity / $setSize);
        $freeItems = $completeSets * $this->get_quantity;

        $pricePerUnit = $basePrice / $quantity;
        $discountAmount = $freeItems * $pricePerUnit;

        return [
            'free_items' => $freeItems,
            'discount_amount' => round($discountAmount, 2),
            'offer_description' => "Buy {$this->buy_quantity} Get {$this->get_quantity} Free",
        ];
    }

    private function calculateQuantityDiscount(float $quantity, float $basePrice): array
    {
        if ($quantity < $this->min_quantity) {
            return [
                'discount_amount' => 0,
                'offer_description' => null,
            ];
        }

        if ($this->discount_type === 'percentage') {
            $discount = ($basePrice * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        return [
            'discount_amount' => round($discount, 2),
            'offer_description' => "Buy {$this->min_quantity}+ get " .
                ($this->discount_type === 'percentage' ? $this->discount_value . '%' : 'Rs. ' . $this->discount_value) . ' off',
        ];
    }
}
