<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories
        $beverages = Category::create([
            'name' => 'Beverages',
            'description' => 'All types of drinks including soft drinks, juices, and water',
            'is_active' => true,
        ]);

        $snacks = Category::create([
            'name' => 'Snacks',
            'description' => 'Chips, crackers, and other snack items',
            'is_active' => true,
        ]);

        $dairy = Category::create([
            'name' => 'Dairy',
            'description' => 'Milk, cheese, yogurt, and other dairy products',
            'is_active' => true,
        ]);

        $bakery = Category::create([
            'name' => 'Bakery',
            'description' => 'Bread, pastries, cakes, and baked goods',
            'is_active' => true,
        ]);

        $household = Category::create([
            'name' => 'Household',
            'description' => 'Cleaning supplies and household items',
            'is_active' => true,
        ]);

        $freshProduce = Category::create([
            'name' => 'Fresh Produce',
            'description' => 'Fresh fruits and vegetables',
            'is_active' => true,
        ]);

        $groceryStaples = Category::create([
            'name' => 'Grocery Staples',
            'description' => 'Rice, flour, sugar, and other essential items',
            'is_active' => true,
        ]);

        $personalCare = Category::create([
            'name' => 'Personal Care',
            'description' => 'Toiletries, cosmetics, and personal hygiene products',
            'is_active' => true,
        ]);

        // Create subcategories for Fresh Produce
        Category::create([
            'name' => 'Vegetables',
            'parent_id' => $freshProduce->id,
            'description' => 'Fresh vegetables including leafy greens, root vegetables, and more',
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Fruits',
            'parent_id' => $freshProduce->id,
            'description' => 'Fresh fruits including seasonal and tropical fruits',
            'is_active' => true,
        ]);
    }
}
