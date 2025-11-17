<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Chart of Accounts Structure:
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

        $this->createAccount([
            'account_code' => '1110',
            'account_name' => 'Cash on Hand',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['cash'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1120',
            'account_name' => 'Petty Cash',
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

        $this->createAccount([
            'account_code' => '1210',
            'account_name' => 'Bank Account 1',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['bank'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1220',
            'account_name' => 'Bank Account 2',
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

        $this->createAccount([
            'account_code' => '1410',
            'account_name' => 'Stock in Hand',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['inventory'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1420',
            'account_name' => 'Damaged Stock',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['inventory'],
            'is_system_account' => true,
        ]);

        // Fixed Assets (1500-1999)
        $parentAccounts['fixed_assets'] = $this->createAccount([
            'account_code' => '1500',
            'account_name' => 'Fixed Assets',
            'account_type' => 'asset',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1510',
            'account_name' => 'Furniture & Fixtures',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['fixed_assets'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1520',
            'account_name' => 'Equipment',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['fixed_assets'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '1530',
            'account_name' => 'Vehicles',
            'account_type' => 'asset',
            'parent_id' => $parentAccounts['fixed_assets'],
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

        $this->createAccount([
            'account_code' => '2110',
            'account_name' => 'Supplier Payables',
            'account_type' => 'liability',
            'parent_id' => $parentAccounts['payables'],
            'is_system_account' => true,
        ]);

        // Other Current Liabilities (2200-2299)
        $this->createAccount([
            'account_code' => '2200',
            'account_name' => 'Sales Tax Payable',
            'account_type' => 'liability',
            'parent_id' => $parentAccounts['current_liabilities'],
            'is_system_account' => true,
        ]);

        // Long-term Liabilities (2500-2999)
        $parentAccounts['long_term_liabilities'] = $this->createAccount([
            'account_code' => '2500',
            'account_name' => 'Long-term Liabilities',
            'account_type' => 'liability',
            'parent_id' => null,
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '2510',
            'account_name' => 'Long-term Loans',
            'account_type' => 'liability',
            'parent_id' => $parentAccounts['long_term_liabilities'],
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

        $this->createAccount([
            'account_code' => '3100',
            'account_name' => 'Capital',
            'account_type' => 'equity',
            'parent_id' => $parentAccounts['equity'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '3200',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'parent_id' => $parentAccounts['equity'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '3300',
            'account_name' => 'Owner\'s Drawings',
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

        $this->createAccount([
            'account_code' => '4110',
            'account_name' => 'Product Sales',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['sales_revenue'],
            'is_system_account' => true,
        ]);

        // Sales Returns & Discounts (4200-4299)
        $this->createAccount([
            'account_code' => '4200',
            'account_name' => 'Sales Returns & Allowances',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['income'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '4210',
            'account_name' => 'Sales Discounts',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['income'],
            'is_system_account' => true,
        ]);

        // Other Income (4300-4999)
        $parentAccounts['other_income'] = $this->createAccount([
            'account_code' => '4300',
            'account_name' => 'Other Income',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['income'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '4310',
            'account_name' => 'Interest Income',
            'account_type' => 'income',
            'parent_id' => $parentAccounts['other_income'],
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

        $this->createAccount([
            'account_code' => '5110',
            'account_name' => 'Purchases',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['cogs'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '5120',
            'account_name' => 'Purchase Returns',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['cogs'],
            'is_system_account' => true,
        ]);

        $this->createAccount([
            'account_code' => '5130',
            'account_name' => 'Stock Write-offs',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['cogs'],
            'is_system_account' => true,
        ]);

        // Operating Expenses (5200-5999)
        $parentAccounts['operating_expenses'] = $this->createAccount([
            'account_code' => '5200',
            'account_name' => 'Operating Expenses',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['expenses'],
            'is_system_account' => true,
        ]);

        // Salaries & Wages (5210-5219)
        $this->createAccount([
            'account_code' => '5210',
            'account_name' => 'Salaries & Wages',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Rent (5220-5229)
        $this->createAccount([
            'account_code' => '5220',
            'account_name' => 'Rent Expense',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Utilities (5230-5239)
        $this->createAccount([
            'account_code' => '5230',
            'account_name' => 'Utilities Expense',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Marketing & Advertising (5240-5249)
        $this->createAccount([
            'account_code' => '5240',
            'account_name' => 'Marketing & Advertising',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Office Supplies (5250-5259)
        $this->createAccount([
            'account_code' => '5250',
            'account_name' => 'Office Supplies',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Maintenance & Repairs (5260-5269)
        $this->createAccount([
            'account_code' => '5260',
            'account_name' => 'Maintenance & Repairs',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Transportation (5270-5279)
        $this->createAccount([
            'account_code' => '5270',
            'account_name' => 'Transportation Expense',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Insurance (5280-5289)
        $this->createAccount([
            'account_code' => '5280',
            'account_name' => 'Insurance Expense',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Depreciation (5290-5299)
        $this->createAccount([
            'account_code' => '5290',
            'account_name' => 'Depreciation Expense',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Bank Charges (5300-5309)
        $this->createAccount([
            'account_code' => '5300',
            'account_name' => 'Bank Charges',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
            'is_system_account' => true,
        ]);

        // Miscellaneous Expenses (5900-5999)
        $this->createAccount([
            'account_code' => '5900',
            'account_name' => 'Miscellaneous Expenses',
            'account_type' => 'expense',
            'parent_id' => $parentAccounts['operating_expenses'],
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
