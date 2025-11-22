<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Simplified Chart of Accounts - Essential Accounts Only
     * For Billing (Sales), GRN (Purchases), and Returns
     *
     * Chart Structure:
     * 1000-1999: Assets
     * 2000-2999: Liabilities
     * 3000-3999: Equity
     * 4000-4999: Income
     * 5000-5999: Expenses
     */
    public function run(): void
    {
        // Store parent account IDs for relationship creation
        $parentAccounts = [];

        // ===========================
        // ASSETS (1000-1999)
        // ===========================

        // Current Assets (1000-1499)
        $parentAccounts['current_assets'] = $this->createAccount([
            'account_code' => '1000',
            'account_name' => 'Current Assets',
            'account_type' => 'asset',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        // Cash & Cash Equivalents (1100-1199)
        $parentAccounts['cash'] = $this->createAccount([
            'account_code' => '1100',
            'account_name' => 'Cash & Cash Equivalents',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['current_assets'],
            'is_system_account' => true,
        ]);

        // 1110: Cash on Hand - Used for cash sales and cash refunds
        $this->createAccount([
            'account_code' => '1110',
            'account_name' => 'Cash on Hand',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['cash'],
            'is_system_account' => true,
        ]);

        // Bank Accounts (1200-1299)
        $parentAccounts['bank'] = $this->createAccount([
            'account_code' => '1200',
            'account_name' => 'Bank Accounts',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['current_assets'],
            'is_system_account' => true,
        ]);

        // 1210: Bank Account - Used for bank transfer sales and payments
        $this->createAccount([
            'account_code' => '1210',
            'account_name' => 'Bank Account',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['bank'],
            'is_system_account' => true,
        ]);

        // Accounts Receivable (1300-1399)
        $parentAccounts['receivables'] = $this->createAccount([
            'account_code' => '1300',
            'account_name' => 'Accounts Receivable',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['current_assets'],
            'is_system_account' => true,
        ]);

        // 1310: Customer Receivables - Used for credit sales
        $this->createAccount([
            'account_code' => '1310',
            'account_name' => 'Customer Receivables',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['receivables'],
            'is_system_account' => true,
        ]);

        // Inventory (1400-1499)
        $parentAccounts['inventory'] = $this->createAccount([
            'account_code' => '1400',
            'account_name' => 'Inventory',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['current_assets'],
            'is_system_account' => true,
        ]);

        // 1410: Stock in Hand - Used for inventory purchases (GRN)
        $this->createAccount([
            'account_code' => '1410',
            'account_name' => 'Stock in Hand',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['inventory'],
            'is_system_account' => true,
        ]);

        // 1420: Damaged Stock - Used for damaged returns and damaged inventory
        $this->createAccount([
            'account_code' => '1420',
            'account_name' => 'Damaged Stock',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['inventory'],
            'is_system_account' => true,
        ]);

        // ===========================
        // LIABILITIES (2000-2999)
        // ===========================

        // Current Liabilities (2000-2499)
        $parentAccounts['current_liabilities'] = $this->createAccount([
            'account_code' => '2000',
            'account_name' => 'Current Liabilities',
            'account_type' => 'liability',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        // Accounts Payable (2100-2199)
        $parentAccounts['payables'] = $this->createAccount([
            'account_code' => '2100',
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'parent_id' => $parentAccounts['current_liabilities'],
            'is_system_account' => true,
        ]);

        // 2110: Supplier Payables - Used for GRN purchases and supplier payments
        $this->createAccount([
            'account_code' => '2110',
            'account_name' => 'Supplier Payables',
            'account_type' => 'liability',
            'parent_id' => $parentAccounts['payables'],
            'is_system_account' => true,
        ]);

        // ===========================
        // EQUITY (3000-3999)
        // ===========================

        $parentAccounts['equity'] = $this->createAccount([
            'account_code' => '3000',
            'account_name' => 'Owner\'s Equity',
            'account_type' => 'equity',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        // 3200: Retained Earnings - Accumulates business profits/losses
        $this->createAccount([
            'account_code' => '3200',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'parent_id' => $parentAccounts['equity'],
            'is_system_account' => true,
        ]);

        // ===========================
        // INCOME (4000-4999)
        // ===========================

        $parentAccounts['income'] = $this->createAccount([
            'account_code' => '4000',
            'account_name' => 'Revenue',
            'account_type' => 'income',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        // Sales Revenue (4100-4199)
        $parentAccounts['sales_revenue'] = $this->createAccount([
            'account_code' => '4100',
            'account_name' => 'Sales Revenue',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['income'],
            'is_system_account' => true,
        ]);

        // 4110: Product Sales - Used for all sales revenue
        $this->createAccount([
            'account_code' => '4110',
            'account_name' => 'Product Sales',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['sales_revenue'],
            'is_system_account' => true,
        ]);

        // 4200: Sales Returns & Allowances - Used for sales returns
        $this->createAccount([
            'account_code' => '4200',
            'account_name' => 'Sales Returns & Allowances',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['income'],
            'is_system_account' => true,
        ]);

        // ===========================
        // EXPENSES (5000-5999)
        // ===========================

        $parentAccounts['expenses'] = $this->createAccount([
            'account_code' => '5000',
            'account_name' => 'Expenses',
            'account_type' => 'expense',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        // Cost of Goods Sold (5100-5199)
        $parentAccounts['cogs'] = $this->createAccount([
            'account_code' => '5100',
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['expenses'],
            'is_system_account' => true,
        ]);

        // 5110: COGS - Merchandise - Used for cost of goods sold on sales
        $this->createAccount([
            'account_code' => '5110',
            'account_name' => 'COGS - Merchandise',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['cogs'],
            'is_system_account' => true,
        ]);

        // 5130: Stock Write-offs - Used for damaged stock write-offs
        $this->createAccount([
            'account_code' => '5130',
            'account_name' => 'Stock Write-offs',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['cogs'],
            'is_system_account' => true,
        ]);
    }

    /**
     * Create or update an account and return its ID
     */
    private function createAccount(array $data): int
    {
        $account = Account::updateOrCreate(
            ['account_code' => $data['account_code']],
            $data
        );

        return $account->id;
    }
}
