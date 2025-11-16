<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'account_code' => 'CASH',
                'account_name' => 'Cash',
                'account_type' => 'asset',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
            [
                'account_code' => 'BANK1',
                'account_name' => 'Bank Account 1',
                'account_type' => 'asset',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
            [
                'account_code' => 'BANK2',
                'account_name' => 'Bank Account 2',
                'account_type' => 'asset',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
            [
                'account_code' => 'SALES',
                'account_name' => 'Sales Revenue',
                'account_type' => 'income',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
            [
                'account_code' => 'PURCHASES',
                'account_name' => 'Purchases',
                'account_type' => 'expense',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
            [
                'account_code' => 'INVENTORY',
                'account_name' => 'Inventory',
                'account_type' => 'asset',
                'parent_id' => null,
                'is_system_account' => true,
                'balance' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['account_code' => $account['account_code']],
                $account
            );
        }
    }
}
