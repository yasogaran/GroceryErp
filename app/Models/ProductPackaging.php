<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackaging extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_packaging';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'packaging_name',
        'pieces_per_package',
        'package_barcode',
        'discount_type',
        'discount_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pieces_per_package' => 'integer',
        'discount_value' => 'decimal:2',
    ];

    /**
     * Get the product that owns the packaging.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the package price based on the product's min selling price and discount.
     */
    public function calculatePackagePrice(): float
    {
        $basePrice = $this->product->min_selling_price * $this->pieces_per_package;

        if ($this->discount_type === 'percentage') {
            return $basePrice - ($basePrice * $this->discount_value / 100);
        }

        return $basePrice - $this->discount_value;
    }

    /**
     * Generate a unique package barcode.
     */
    public static function generateUniqueBarcode(): string
    {
        do {
            // Generate a 13-digit EAN-13 barcode starting with 21 for packages
            $barcode = '21' . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
        } while (self::where('package_barcode', $barcode)->exists() || Product::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
