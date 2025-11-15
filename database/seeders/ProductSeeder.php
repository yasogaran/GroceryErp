<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', 'admin@grocery.com')->first();

        // Get some categories
        $beverages = Category::where('name', 'Beverages')->first();
        $snacks = Category::where('name', 'Snacks')->first();
        $dairy = Category::where('name', 'Dairy Products')->first();

        // If categories don't exist, create them
        if (!$beverages) {
            $beverages = Category::create([
                'name' => 'Beverages',
                'description' => 'Soft drinks, juices, and other beverages',
                'is_active' => true,
            ]);
        }

        if (!$snacks) {
            $snacks = Category::create([
                'name' => 'Snacks',
                'description' => 'Chips, crackers, and other snack items',
                'is_active' => true,
            ]);
        }

        if (!$dairy) {
            $dairy = Category::create([
                'name' => 'Dairy Products',
                'description' => 'Milk, cheese, yogurt, and other dairy items',
                'is_active' => true,
            ]);
        }

        // Product 1: Coca Cola 330ml (with packaging)
        $cocaCola = Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2001234567890',
            'name' => 'Coca Cola 330ml',
            'description' => 'Refreshing cola soft drink in 330ml can',
            'category_id' => $beverages->id,
            'brand' => 'Coca Cola',
            'base_unit' => 'piece',
            'min_selling_price' => 1.50,
            'max_selling_price' => 2.00,
            'current_stock_quantity' => 150,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 50,
            'is_active' => true,
            'has_packaging' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Add packaging for Coca Cola
        ProductPackaging::create([
            'product_id' => $cocaCola->id,
            'packaging_name' => 'Box',
            'pieces_per_package' => 24,
            'package_barcode' => '2101234567890',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        // Product 2: Pepsi 500ml (with packaging)
        $pepsi = Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2002234567890',
            'name' => 'Pepsi 500ml',
            'description' => 'Refreshing cola soft drink in 500ml bottle',
            'category_id' => $beverages->id,
            'brand' => 'Pepsi',
            'base_unit' => 'piece',
            'min_selling_price' => 1.75,
            'max_selling_price' => 2.25,
            'current_stock_quantity' => 100,
            'damaged_stock_quantity' => 5,
            'reorder_level' => 40,
            'is_active' => true,
            'has_packaging' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        ProductPackaging::create([
            'product_id' => $pepsi->id,
            'packaging_name' => 'Carton',
            'pieces_per_package' => 12,
            'package_barcode' => '2102234567890',
            'discount_type' => 'fixed',
            'discount_value' => 2.00,
        ]);

        // Product 3: Lays Chips Classic 50g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2003234567890',
            'name' => 'Lays Chips Classic 50g',
            'description' => 'Classic salted potato chips',
            'category_id' => $snacks->id,
            'brand' => 'Lays',
            'base_unit' => 'piece',
            'min_selling_price' => 1.00,
            'max_selling_price' => 1.50,
            'current_stock_quantity' => 200,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 30,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 4: Doritos Nacho Cheese 100g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2004234567890',
            'name' => 'Doritos Nacho Cheese 100g',
            'description' => 'Nacho cheese flavored tortilla chips',
            'category_id' => $snacks->id,
            'brand' => 'Doritos',
            'base_unit' => 'piece',
            'min_selling_price' => 2.00,
            'max_selling_price' => 2.75,
            'current_stock_quantity' => 80,
            'damaged_stock_quantity' => 2,
            'reorder_level' => 25,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 5: Fresh Milk 1L
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2005234567890',
            'name' => 'Fresh Milk 1L',
            'description' => 'Fresh whole milk, pasteurized',
            'category_id' => $dairy->id,
            'brand' => 'Dairy Fresh',
            'base_unit' => 'liter',
            'min_selling_price' => 3.50,
            'max_selling_price' => 4.50,
            'current_stock_quantity' => 45,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 50,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 6: Greek Yogurt 500g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2006234567890',
            'name' => 'Greek Yogurt 500g',
            'description' => 'Creamy Greek style yogurt',
            'category_id' => $dairy->id,
            'brand' => 'Chobani',
            'base_unit' => 'g',
            'min_selling_price' => 4.00,
            'max_selling_price' => 5.50,
            'current_stock_quantity' => 30,
            'damaged_stock_quantity' => 1,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 7: Orange Juice 1L (with packaging)
        $orangeJuice = Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2007234567890',
            'name' => 'Orange Juice 1L',
            'description' => '100% pure orange juice, no added sugar',
            'category_id' => $beverages->id,
            'brand' => 'Tropicana',
            'base_unit' => 'liter',
            'min_selling_price' => 3.00,
            'max_selling_price' => 4.00,
            'current_stock_quantity' => 60,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 30,
            'is_active' => true,
            'has_packaging' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        ProductPackaging::create([
            'product_id' => $orangeJuice->id,
            'packaging_name' => 'Box',
            'pieces_per_package' => 6,
            'package_barcode' => '2107234567890',
            'discount_type' => 'percentage',
            'discount_value' => 5,
        ]);

        // Product 8: Cheddar Cheese 200g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2008234567890',
            'name' => 'Cheddar Cheese 200g',
            'description' => 'Aged cheddar cheese block',
            'category_id' => $dairy->id,
            'brand' => 'Kraft',
            'base_unit' => 'g',
            'min_selling_price' => 5.00,
            'max_selling_price' => 6.50,
            'current_stock_quantity' => 25,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 15,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 9: Pringles Original 150g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2009234567890',
            'name' => 'Pringles Original 150g',
            'description' => 'Stackable potato crisps, original flavor',
            'category_id' => $snacks->id,
            'brand' => 'Pringles',
            'base_unit' => 'piece',
            'min_selling_price' => 2.50,
            'max_selling_price' => 3.50,
            'current_stock_quantity' => 70,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 10: Sprite 330ml (with packaging)
        $sprite = Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2010234567890',
            'name' => 'Sprite 330ml',
            'description' => 'Lemon-lime flavored soft drink',
            'category_id' => $beverages->id,
            'brand' => 'Sprite',
            'base_unit' => 'piece',
            'min_selling_price' => 1.50,
            'max_selling_price' => 2.00,
            'current_stock_quantity' => 120,
            'damaged_stock_quantity' => 3,
            'reorder_level' => 50,
            'is_active' => true,
            'has_packaging' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        ProductPackaging::create([
            'product_id' => $sprite->id,
            'packaging_name' => 'Box',
            'pieces_per_package' => 24,
            'package_barcode' => '2110234567890',
            'discount_type' => 'percentage',
            'discount_value' => 8,
        ]);
    }
}
