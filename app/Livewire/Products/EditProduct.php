<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPackaging;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditProduct extends Component
{
    use WithFileUploads;

    public $productId;

    // Basic Info
    public $sku = '';
    public $barcode = '';
    public $name = '';
    public $description = '';
    public $category_id = null;
    public $brand = '';
    public $base_unit = 'piece';

    // Pricing
    public $min_selling_price = '';
    public $max_selling_price = '';

    // Stock levels (read-only for now)
    public $current_stock_quantity = 0;
    public $damaged_stock_quantity = 0;
    public $reorder_level = 0;

    // Image
    public $image = null;
    public $existing_image_path = null;

    // Status
    public $is_active = true;

    // Packaging
    public $has_packaging = false;
    public $packaging_id = null;
    public $packaging_name = '';
    public $pieces_per_package = '';
    public $package_barcode = '';
    public $discount_type = 'percentage';
    public $discount_value = 0;

    // UI State
    public $activeTab = 'basic';

    public $baseUnits = [
        'piece' => 'Piece',
        'kg' => 'Kilogram (kg)',
        'g' => 'Gram (g)',
        'liter' => 'Liter (L)',
        'ml' => 'Milliliter (ml)',
        'dozen' => 'Dozen',
    ];

    /**
     * Mount the component.
     */
    public function mount($productId)
    {
        $this->productId = $productId;
        $product = Product::with('packaging')->findOrFail($productId);

        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->brand = $product->brand;
        $this->base_unit = $product->base_unit;
        $this->min_selling_price = $product->min_selling_price;
        $this->max_selling_price = $product->max_selling_price;
        $this->current_stock_quantity = $product->current_stock_quantity;
        $this->damaged_stock_quantity = $product->damaged_stock_quantity;
        $this->reorder_level = $product->reorder_level;
        $this->existing_image_path = $product->image_path;
        $this->is_active = $product->is_active;
        $this->has_packaging = $product->has_packaging;

        // Load packaging if exists
        if ($product->has_packaging && $product->packaging) {
            $packaging = $product->packaging;
            $this->packaging_id = $packaging->id;
            $this->packaging_name = $packaging->packaging_name;
            $this->pieces_per_package = $packaging->pieces_per_package;
            $this->package_barcode = $packaging->package_barcode;
            $this->discount_type = $packaging->discount_type;
            $this->discount_value = $packaging->discount_value;
        }
    }

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku,' . $this->productId],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,' . $this->productId],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'base_unit' => ['required', 'string', 'max:50'],
            'min_selling_price' => ['required', 'numeric', 'min:0'],
            'max_selling_price' => ['required', 'numeric', 'min:0', 'gte:min_selling_price'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB max
            'is_active' => ['boolean'],
            'has_packaging' => ['boolean'],
            'packaging_name' => ['required_if:has_packaging,true', 'nullable', 'string', 'max:255'],
            'pieces_per_package' => ['required_if:has_packaging,true', 'nullable', 'integer', 'min:1'],
            'package_barcode' => ['nullable', 'string', 'max:255', 'unique:product_packaging,package_barcode,' . ($this->packaging_id ?? 'NULL')],
            'discount_type' => ['required_if:has_packaging,true', 'in:percentage,fixed'],
            'discount_value' => ['required_if:has_packaging,true', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected $messages = [
        'name.required' => 'Product name is required.',
        'sku.required' => 'SKU is required.',
        'sku.unique' => 'This SKU already exists.',
        'barcode.unique' => 'This barcode already exists.',
        'category_id.required' => 'Category is required.',
        'category_id.exists' => 'The selected category does not exist.',
        'min_selling_price.required' => 'Minimum selling price is required.',
        'max_selling_price.required' => 'Maximum selling price (MRP) is required.',
        'max_selling_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
        'image.max' => 'Image size must not exceed 2MB.',
        'packaging_name.required_if' => 'Packaging name is required when packaging is enabled.',
        'pieces_per_package.required_if' => 'Pieces per package is required when packaging is enabled.',
        'package_barcode.unique' => 'This package barcode already exists.',
    ];

    /**
     * Generate a unique barcode for the product.
     */
    public function generateBarcode()
    {
        $this->barcode = Product::generateUniqueBarcode();
    }

    /**
     * Generate a unique barcode for the package.
     */
    public function generatePackageBarcode()
    {
        $this->package_barcode = ProductPackaging::generateUniqueBarcode();
    }

    /**
     * Switch to a different tab.
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Update the product.
     */
    public function update()
    {
        $validated = $this->validate();

        $product = Product::findOrFail($this->productId);

        // Handle image upload
        $imagePath = $product->image_path;
        if ($this->image) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $imagePath = $this->image->store('products', 'public');
        }

        // Convert empty strings to null for nullable fields
        $barcode = !empty($validated['barcode']) ? $validated['barcode'] : null;
        $description = !empty($validated['description']) ? $validated['description'] : null;
        $brand = !empty($validated['brand']) ? $validated['brand'] : null;

        // Update the product
        $product->update([
            'sku' => $validated['sku'],
            'barcode' => $barcode,
            'name' => $validated['name'],
            'description' => $description,
            'category_id' => $validated['category_id'],
            'brand' => $brand,
            'base_unit' => $validated['base_unit'],
            'min_selling_price' => $validated['min_selling_price'],
            'max_selling_price' => $validated['max_selling_price'],
            'reorder_level' => $validated['reorder_level'],
            'image_path' => $imagePath,
            'is_active' => $validated['is_active'],
            'has_packaging' => $validated['has_packaging'],
            'updated_by' => auth()->id(),
        ]);

        // Handle packaging
        if ($this->has_packaging) {
            $packageBarcode = !empty($validated['package_barcode']) ? $validated['package_barcode'] : null;

            if ($this->packaging_id) {
                // Update existing packaging
                ProductPackaging::where('id', $this->packaging_id)->update([
                    'packaging_name' => $validated['packaging_name'],
                    'pieces_per_package' => $validated['pieces_per_package'],
                    'package_barcode' => $packageBarcode,
                    'discount_type' => $validated['discount_type'],
                    'discount_value' => $validated['discount_value'],
                ]);
            } else {
                // Create new packaging
                ProductPackaging::create([
                    'product_id' => $product->id,
                    'packaging_name' => $validated['packaging_name'],
                    'pieces_per_package' => $validated['pieces_per_package'],
                    'package_barcode' => $packageBarcode,
                    'discount_type' => $validated['discount_type'],
                    'discount_value' => $validated['discount_value'],
                ]);
            }
        } else {
            // Remove packaging if disabled
            if ($this->packaging_id) {
                ProductPackaging::where('id', $this->packaging_id)->delete();
            }
        }

        session()->flash('success', 'Product updated successfully.');

        $this->dispatch('product-updated');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('livewire.products.edit-product', [
            'categories' => $categories,
        ]);
    }
}
