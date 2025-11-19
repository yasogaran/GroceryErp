<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Sale;
use App\Models\JournalEntry;
use Illuminate\Console\Command;
use Database\Seeders\AccountSeeder;

class VerifyCashAccountSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash:verify-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and fix cash account tracking setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Cash Account Setup Verification ===');
        $this->newLine();

        // Check 1: Account 1110 exists
        $this->info('1. Checking if Account 1110 (Cash on Hand) exists...');
        $cashAccount = Account::where('account_code', '1110')->first();

        if ($cashAccount) {
            $this->info("   ✓ Cash on Hand account exists");
            $this->info("     - ID: {$cashAccount->id}");
            $this->info("     - Name: {$cashAccount->account_name}");
            $this->info("     - Current Balance: Rs. " . number_format($cashAccount->balance, 2));
        } else {
            $this->error("   ✗ Cash on Hand account (1110) NOT FOUND!");
            $this->newLine();

            if ($this->confirm('Would you like to run the AccountSeeder to create it?', true)) {
                $this->info('   Running AccountSeeder...');
                $seeder = new AccountSeeder();
                $seeder->run();
                $this->info('   ✓ AccountSeeder completed');

                // Re-check
                $cashAccount = Account::where('account_code', '1110')->first();
                if ($cashAccount) {
                    $this->info("   ✓ Cash on Hand account created successfully!");
                }
            }
        }

        $this->newLine();

        // Check 2: Other required accounts
        $this->info('2. Checking other required accounts...');
        $requiredAccounts = [
            '4110' => 'Product Sales (Revenue)',
            '1210' => 'BOC Bank Account',
            '1310' => 'Customer Receivables',
            '4200' => 'Sales Returns & Allowances',
            '2110' => 'Supplier Payables',
            '1410' => 'Stock in Hand',
        ];

        $allExist = true;
        foreach ($requiredAccounts as $code => $name) {
            $account = Account::where('account_code', $code)->first();
            if ($account) {
                $this->info("   ✓ {$code} - {$name}");
            } else {
                $this->error("   ✗ {$code} - {$name} NOT FOUND");
                $allExist = false;
            }
        }

        if (!$allExist) {
            $this->newLine();
            $this->warn('   Some accounts are missing. Run: php artisan db:seed --class=AccountSeeder');
        }

        $this->newLine();

        // Check 3: Recent sales
        $this->info('3. Checking recent sales...');
        $recentSales = Sale::orderBy('created_at', 'desc')->limit(5)->get();

        if ($recentSales->isEmpty()) {
            $this->warn('   No sales found in database');
        } else {
            $this->info("   Found {$recentSales->count()} recent sale(s):");
            foreach ($recentSales as $sale) {
                $this->info("     - {$sale->invoice_number} | Rs. {$sale->total_amount} | {$sale->created_at->format('Y-m-d H:i')}");
            }
        }

        $this->newLine();

        // Check 4: Journal entries for sales
        $this->info('4. Checking journal entries for sales...');
        $journalEntries = JournalEntry::where('reference_type', Sale::class)
            ->where('status', 'posted')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($journalEntries->isEmpty()) {
            $this->error('   ✗ NO journal entries found for sales!');
            $this->newLine();
            $this->warn('   This is the problem! Sales are not being posted to accounting.');
            $this->warn('   The SaleObserver should be creating journal entries automatically.');
            $this->newLine();
            $this->info('   Possible causes:');
            $this->info('   1. Observers not registered (check app/Providers/AppServiceProvider.php)');
            $this->info('   2. Sales created before observer was added');
            $this->info('   3. Error in SaleObserver (check logs)');
            $this->newLine();

            if (!$recentSales->isEmpty() && $this->confirm('Would you like to manually post existing sales to accounting?', true)) {
                $this->info('   Posting sales to accounting...');
                $transactionService = app(\App\Services\TransactionService::class);
                $count = 0;

                foreach (Sale::where('status', 'completed')->get() as $sale) {
                    try {
                        if (!$transactionService->isPosted(Sale::class, $sale->id)) {
                            $transactionService->postSale($sale);
                            $count++;
                            $this->info("     ✓ Posted {$sale->invoice_number}");
                        }
                    } catch (\Exception $e) {
                        $this->error("     ✗ Failed to post {$sale->invoice_number}: " . $e->getMessage());
                    }
                }

                $this->info("   ✓ Posted {$count} sale(s) to accounting");
            }
        } else {
            $this->info("   ✓ Found {$journalEntries->count()} journal entry(s):");
            foreach ($journalEntries as $entry) {
                $this->info("     - {$entry->entry_number} | Rs. {$entry->total_debit} | {$entry->created_at->format('Y-m-d H:i')}");
            }
        }

        $this->newLine();

        // Check 5: Cash account balance from journal entries
        if ($cashAccount) {
            $this->info('5. Checking cash account balance from journal entries...');

            $result = \App\Models\JournalEntryLine::where('account_id', $cashAccount->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted');
                })
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit, COUNT(*) as transaction_count')
                ->first();

            $totalDebit = floatval($result->total_debit ?? 0);
            $totalCredit = floatval($result->total_credit ?? 0);
            $balance = $totalDebit - $totalCredit;
            $count = $result->transaction_count ?? 0;

            $this->info("   Total Transactions: {$count}");
            $this->info("   Total Debits (Cash In): Rs. " . number_format($totalDebit, 2));
            $this->info("   Total Credits (Cash Out): Rs. " . number_format($totalCredit, 2));
            $this->info("   Calculated Balance: Rs. " . number_format($balance, 2));
            $this->info("   Account Balance Field: Rs. " . number_format($cashAccount->balance, 2));

            if ($count == 0) {
                $this->error('   ✗ No transactions found for cash account!');
                $this->warn('   This means journal entries are not being created with cash account (1110)');
            } else {
                $this->info('   ✓ Cash transactions are being recorded');
            }
        }

        $this->newLine();
        $this->info('=== Verification Complete ===');
        $this->newLine();

        // Summary
        if ($cashAccount && $allExist) {
            if ($journalEntries->isEmpty()) {
                $this->warn('⚠ Setup is OK, but sales are not being posted to accounting.');
                $this->info('  Create a new sale to test if the observer is working.');
            } else {
                $this->info('✓ Everything looks good! Cash tracking should be working.');
            }
        } else {
            $this->error('✗ Setup issues found. Please fix the issues above.');
        }

        return 0;
    }
}
