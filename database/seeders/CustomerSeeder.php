<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the default walk-in customer
        Customer::create([
            'customer_code' => 'WALK-IN',
            'name' => 'Walk-in Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'points_balance' => 0,
            'total_purchases' => 0,
            'is_active' => true,
        ]);

        // Create sample customers
        Customer::factory(15)->create();
    }
}
