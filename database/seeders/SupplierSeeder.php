<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'ABC Wholesalers Pvt Ltd',
                'contact_person' => 'Rajesh Kumar',
                'email' => 'rajesh@abcwholesalers.com',
                'phone' => '9876543210',
                'address' => 'Plot No. 45, Industrial Area, Sector 8',
                'city' => 'Mumbai',
                'credit_terms' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Foods Distribution',
                'contact_person' => 'Priya Sharma',
                'email' => 'priya@freshfoods.com',
                'phone' => '9123456789',
                'address' => 'Building 12, Market Complex',
                'city' => 'Delhi',
                'credit_terms' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Quality Grocers Ltd',
                'contact_person' => 'Amit Patel',
                'email' => 'amit@qualitygrocers.com',
                'phone' => '9988776655',
                'address' => 'Shop No. 789, Main Road',
                'city' => 'Bangalore',
                'credit_terms' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Metro Supplies',
                'contact_person' => 'Sunita Devi',
                'email' => 'sunita@metrosupplies.com',
                'phone' => '9445566778',
                'address' => 'Warehouse 5, MIDC Area',
                'city' => 'Pune',
                'credit_terms' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Supreme Distributors',
                'contact_person' => 'Vikram Singh',
                'email' => 'vikram@supremedist.com',
                'phone' => '9234567890',
                'address' => 'Unit 15, Trade Center',
                'city' => 'Chennai',
                'credit_terms' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Golden Traders',
                'contact_person' => 'Lakshmi Reddy',
                'email' => 'lakshmi@goldentraders.com',
                'phone' => '9876512340',
                'address' => 'Shop 456, Commercial Street',
                'city' => 'Hyderabad',
                'credit_terms' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Royal Foods & Beverages',
                'contact_person' => 'Arjun Mehta',
                'email' => 'arjun@royalfoods.com',
                'phone' => '9654321098',
                'address' => 'Godown 23, Industrial Estate',
                'city' => 'Ahmedabad',
                'credit_terms' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'City Wholesale Market',
                'contact_person' => 'Neha Gupta',
                'email' => 'neha@citywholesale.com',
                'phone' => '9321098765',
                'address' => 'Block A, Wholesale Market Complex',
                'city' => 'Kolkata',
                'credit_terms' => 7,
                'is_active' => false,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
