<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GRNItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grn_id',
        'product_id',
        'received_boxes',
        'received_pieces',
        'unit_price',
        'min_selling_price',
        'max_selling_price',
        'total_amount',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'received_boxes' => 'integer',
        'received_pieces' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'max_selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grn_items';

    /**
     * Get the GRN that owns the item.
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class, 'grn_id');
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate pieces from boxes for a product.
     *
     * @param Product $product
     * @param int $boxes
     * @return float
     */
    public static function calculatePieces(Product $product, int $boxes): float
    {
        if ($product->has_packaging) {
            $packaging = $product->packaging()->first();
            if ($packaging) {
                return $boxes * $packaging->pieces_per_package;
            }
        }

        return 0;
    }

    /**
     * Calculate total amount.
     *
     * @return float
     */
    public function calculateTotalAmount(): float
    {
        return $this->received_pieces * $this->unit_price;
    }
}
