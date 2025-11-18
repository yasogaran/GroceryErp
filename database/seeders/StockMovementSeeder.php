<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', 'admin@example.com')->first();

        // Get all products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        // Generate 40 random stock movements
        $batchNumbers = ['BATCH-2024-001', 'BATCH-2024-002', 'BATCH-2024-003', 'BATCH-2024-004', 'BATCH-2024-005'];

        for ($i = 0; $i < 40; $i++) {
            // Pick a random product
            $product = $products->random();

            // Determine movement type (80% stock in, 20% adjustment)
            $movementType = rand(1, 100) <= 80 ? 'in' : 'adjustment';

            // Generate random quantities based on product type
            $baseQuantity = rand(10, 100);

            // For stock in movements
            if ($movementType === 'in') {
                $quantity = $baseQuantity;

                // Generate cost prices (70-85% of max selling price)
                $unitCost = $product->max_selling_price * (rand(70, 85) / 100);

                // Manufacturing date (random between 1-60 days ago)
                $manufacturingDate = Carbon::now()->subDays(rand(1, 60));

                // Expiry date (random between 90-365 days from manufacturing)
                $expiryDate = (clone $manufacturingDate)->addDays(rand(90, 365));

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'in',
                    'quantity' => $quantity,
                    'reference_type' => 'grn',
                    'reference_id' => rand(1, 10),
                    'batch_number' => $batchNumbers[array_rand($batchNumbers)],
                    'manufacturing_date' => $manufacturingDate,
                    'expiry_date' => $expiryDate,
                    'unit_cost' => round($unitCost, 2),
                    'min_selling_price' => $product->min_selling_price,
                    'max_selling_price' => $product->max_selling_price,
                    'performed_by' => $admin->id,
                    'notes' => 'Initial stock from supplier',
                    'created_by' => $admin->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);

                // Update product stock
                $product->increment('current_stock_quantity', $quantity);

            } else {
                // Stock adjustment (can be positive or negative)
                $quantity = rand(1, 10) * (rand(0, 1) ? 1 : -1);

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'adjustment',
                    'quantity' => $quantity,
                    'reference_type' => 'manual',
                    'reference_id' => null,
                    'batch_number' => null,
                    'manufacturing_date' => null,
                    'expiry_date' => null,
                    'unit_cost' => null,
                    'min_selling_price' => null,
                    'max_selling_price' => null,
                    'performed_by' => $admin->id,
                    'notes' => $quantity > 0 ? 'Stock count adjustment - found extra' : 'Stock count adjustment - discrepancy',
                    'created_by' => $admin->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 15)),
                ]);

                // Update product stock (ensure it doesn't go negative)
                if ($quantity < 0 && abs($quantity) > $product->current_stock_quantity) {
                    $quantity = -$product->current_stock_quantity;
                }

                $product->increment('current_stock_quantity', $quantity);
            }
        }

        $this->command->info('40 stock movements created successfully!');
    }
}
