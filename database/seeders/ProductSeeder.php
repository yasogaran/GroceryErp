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
        $admin = User::where('email', 'admin@example.com')->first();

        // Get categories
        $beverages = Category::where('name', 'Beverages')->first();
        $snacks = Category::where('name', 'Snacks')->first();
        $dairy = Category::where('name', 'Dairy')->first();
        $bakery = Category::where('name', 'Bakery')->first();
        $household = Category::where('name', 'Household')->first();
        $groceryStaples = Category::where('name', 'Grocery Staples')->first();
        $personalCare = Category::where('name', 'Personal Care')->first();

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

        // Product 11: Basmati Rice 5kg
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2011234567890',
            'name' => 'Basmati Rice 5kg',
            'description' => 'Premium quality aged basmati rice',
            'category_id' => $groceryStaples->id,
            'brand' => 'Royal Umbrella',
            'base_unit' => 'kg',
            'min_selling_price' => 450.00,
            'max_selling_price' => 550.00,
            'current_stock_quantity' => 40,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 15,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 12: White Bread Loaf
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2012234567890',
            'name' => 'White Bread Loaf',
            'description' => 'Freshly baked white bread',
            'category_id' => $bakery->id,
            'brand' => 'Harvest Gold',
            'base_unit' => 'piece',
            'min_selling_price' => 65.00,
            'max_selling_price' => 85.00,
            'current_stock_quantity' => 25,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 10,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 13: Dish Soap 500ml
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2013234567890',
            'name' => 'Dish Soap 500ml',
            'description' => 'Lemon scented dishwashing liquid',
            'category_id' => $household->id,
            'brand' => 'Sunlight',
            'base_unit' => 'ml',
            'min_selling_price' => 120.00,
            'max_selling_price' => 165.00,
            'current_stock_quantity' => 55,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 14: Toothpaste 100g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2014234567890',
            'name' => 'Toothpaste 100g',
            'description' => 'Fluoride toothpaste for cavity protection',
            'category_id' => $personalCare->id,
            'brand' => 'Colgate',
            'base_unit' => 'g',
            'min_selling_price' => 95.00,
            'max_selling_price' => 135.00,
            'current_stock_quantity' => 80,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 30,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 15: Sugar 1kg
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2015234567890',
            'name' => 'White Sugar 1kg',
            'description' => 'Pure white refined sugar',
            'category_id' => $groceryStaples->id,
            'brand' => 'Pelwatte',
            'base_unit' => 'kg',
            'min_selling_price' => 185.00,
            'max_selling_price' => 220.00,
            'current_stock_quantity' => 100,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 25,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 16: Cookies Chocolate Chip 200g
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2016234567890',
            'name' => 'Chocolate Chip Cookies 200g',
            'description' => 'Crunchy cookies with chocolate chips',
            'category_id' => $bakery->id,
            'brand' => 'Munchee',
            'base_unit' => 'g',
            'min_selling_price' => 140.00,
            'max_selling_price' => 180.00,
            'current_stock_quantity' => 65,
            'damaged_stock_quantity' => 2,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 17: Laundry Detergent 1kg
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2017234567890',
            'name' => 'Laundry Detergent Powder 1kg',
            'description' => 'Powerful cleaning detergent powder',
            'category_id' => $household->id,
            'brand' => 'Surf',
            'base_unit' => 'kg',
            'min_selling_price' => 320.00,
            'max_selling_price' => 410.00,
            'current_stock_quantity' => 35,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 15,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 18: Shampoo 200ml
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2018234567890',
            'name' => 'Anti-Dandruff Shampoo 200ml',
            'description' => 'Clinically proven anti-dandruff shampoo',
            'category_id' => $personalCare->id,
            'brand' => 'Head & Shoulders',
            'base_unit' => 'ml',
            'min_selling_price' => 420.00,
            'max_selling_price' => 550.00,
            'current_stock_quantity' => 45,
            'damaged_stock_quantity' => 1,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 19: Cooking Oil 1L
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2019234567890',
            'name' => 'Sunflower Cooking Oil 1L',
            'description' => 'Pure sunflower cooking oil',
            'category_id' => $groceryStaples->id,
            'brand' => 'Fortune',
            'base_unit' => 'liter',
            'min_selling_price' => 520.00,
            'max_selling_price' => 650.00,
            'current_stock_quantity' => 50,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 20,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Product 20: Tissue Box 100 sheets
        Product::create([
            'sku' => 'PRD-' . strtoupper(substr(uniqid(), -8)),
            'barcode' => '2020234567890',
            'name' => 'Facial Tissue Box 100 sheets',
            'description' => 'Soft facial tissues',
            'category_id' => $household->id,
            'brand' => 'Kleenex',
            'base_unit' => 'box',
            'min_selling_price' => 85.00,
            'max_selling_price' => 120.00,
            'current_stock_quantity' => 90,
            'damaged_stock_quantity' => 0,
            'reorder_level' => 30,
            'is_active' => true,
            'has_packaging' => false,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }
}
