<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    protected $generatorPNG;
    protected $generatorSVG;

    public function __construct()
    {
        $this->generatorPNG = new BarcodeGeneratorPNG();
        $this->generatorSVG = new BarcodeGeneratorSVG();
    }

    /**
     * Generate barcode for a product as PNG.
     *
     * @param Product $product
     * @param string $type Type of barcode (barcode or sku)
     * @param int $widthFactor
     * @param int $height
     * @return string Base64 encoded PNG image
     */
    public function generatePNG(Product $product, string $type = 'barcode', int $widthFactor = 2, int $height = 50): string
    {
        $code = $type === 'sku' ? $product->sku : $product->barcode;
        $barcodeFormat = $this->getBarcodeFormat();

        $barcode = $this->generatorPNG->getBarcode($code, $barcodeFormat, $widthFactor, $height);

        return 'data:image/png;base64,' . base64_encode($barcode);
    }

    /**
     * Generate barcode for a product as SVG.
     *
     * @param Product $product
     * @param string $type Type of barcode (barcode or sku)
     * @return string SVG string
     */
    public function generateSVG(Product $product, string $type = 'barcode'): string
    {
        $code = $type === 'sku' ? $product->sku : $product->barcode;
        $barcodeFormat = $this->getBarcodeFormat();

        return $this->generatorSVG->getBarcode($code, $barcodeFormat);
    }

    /**
     * Get barcode format from settings.
     *
     * @return string
     */
    protected function getBarcodeFormat(): string
    {
        $format = Setting::get('barcode_format', 'CODE128');

        return match($format) {
            'CODE128' => BarcodeGeneratorPNG::TYPE_CODE_128,
            'EAN13' => BarcodeGeneratorPNG::TYPE_EAN_13,
            'EAN8' => BarcodeGeneratorPNG::TYPE_EAN_8,
            'UPC' => BarcodeGeneratorPNG::TYPE_UPC_A,
            'CODE39' => BarcodeGeneratorPNG::TYPE_CODE_39,
            default => BarcodeGeneratorPNG::TYPE_CODE_128,
        };
    }

    /**
     * Generate product label data.
     *
     * @param Product $product
     * @param int $quantity Number of labels to generate
     * @return array
     */
    public function generateProductLabel(Product $product, int $quantity = 1): array
    {
        return [
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'price' => $product->min_selling_price,
            'barcode_image' => $this->generatePNG($product, 'barcode', 2, 40),
            'quantity' => $quantity,
        ];
    }

    /**
     * Generate box label data for packaged products.
     *
     * @param Product $product
     * @param int $quantity Number of labels to generate
     * @return array|null
     */
    public function generateBoxLabel(Product $product, int $quantity = 1): ?array
    {
        if (!$product->has_packaging || !$product->packaging) {
            return null;
        }

        $packaging = $product->packaging;

        return [
            'product_name' => $product->name,
            'sku' => $product->sku,
            'package_barcode' => $packaging->package_barcode,
            'units_per_package' => $packaging->units_per_package,
            'package_price' => $packaging->package_selling_price,
            'barcode_image' => $this->generatePNGFromCode($packaging->package_barcode),
            'quantity' => $quantity,
        ];
    }

    /**
     * Generate barcode from a code string.
     *
     * @param string $code
     * @param int $widthFactor
     * @param int $height
     * @return string Base64 encoded PNG image
     */
    public function generatePNGFromCode(string $code, int $widthFactor = 2, int $height = 50): string
    {
        $barcodeFormat = $this->getBarcodeFormat();
        $barcode = $this->generatorPNG->getBarcode($code, $barcodeFormat, $widthFactor, $height);

        return 'data:image/png;base64,' . base64_encode($barcode);
    }

    /**
     * Generate barcode for batch printing.
     *
     * @param array $productIds Array of product IDs with quantities
     * @return array
     */
    public function generateBatchLabels(array $productIds): array
    {
        $labels = [];

        foreach ($productIds as $productId => $quantity) {
            $product = Product::with('packaging')->find($productId);

            if ($product) {
                for ($i = 0; $i < $quantity; $i++) {
                    $labels[] = $this->generateProductLabel($product, 1);
                }
            }
        }

        return $labels;
    }

    /**
     * Get available barcode formats.
     *
     * @return array
     */
    public static function getAvailableFormats(): array
    {
        return [
            'CODE128' => 'CODE 128 (Recommended)',
            'EAN13' => 'EAN-13',
            'EAN8' => 'EAN-8',
            'UPC' => 'UPC-A',
            'CODE39' => 'CODE 39',
        ];
    }

    /**
     * Validate barcode format.
     *
     * @param string $code
     * @param string $format
     * @return bool
     */
    public static function validateBarcodeFormat(string $code, string $format): bool
    {
        return match($format) {
            'EAN13' => strlen($code) === 13 && is_numeric($code),
            'EAN8' => strlen($code) === 8 && is_numeric($code),
            'UPC' => strlen($code) === 12 && is_numeric($code),
            'CODE128', 'CODE39' => strlen($code) > 0,
            default => false,
        };
    }
}
