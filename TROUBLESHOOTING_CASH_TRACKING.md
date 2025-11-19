# Troubleshooting Cash Account Tracking

## Problem: Daily Report Shows Sales but Cash Account Activity is 0

If you see sales in the Daily Sales Report but the "Cash Account Activity" section shows 0 balance or no transactions, follow these steps:

### Step 1: Run the Verification Command

```bash
php artisan cash:verify-setup
```

This command will:
- ✓ Check if Account 1110 (Cash on Hand) exists
- ✓ Check if all required accounts exist
- ✓ Check if journal entries are being created for sales
- ✓ Show cash account transactions
- ✓ Offer to fix issues automatically

### Step 2: Common Issues and Solutions

#### Issue 1: Account 1110 Does Not Exist

**Symptom:** Verification command shows "Cash on Hand account (1110) NOT FOUND!"

**Solution:**
```bash
php artisan db:seed --class=AccountSeeder
```

Or let the verification command create it for you when prompted.

---

#### Issue 2: Sales Exist But No Journal Entries

**Symptom:**
- Sales show up in Daily Sales Report
- Verification command shows "NO journal entries found for sales!"
- Cash Account Activity shows 0

**Cause:** Sales were created BEFORE the observers were added/registered.

**Solution:**

**Option A - Use the Verification Command (Recommended):**
```bash
php artisan cash:verify-setup
```
When prompted "Would you like to manually post existing sales to accounting?", select Yes.

**Option B - Manual Fix (if you prefer):**

Create and run this script:
```bash
php artisan tinker
```

Then paste:
```php
$transactionService = new App\Services\TransactionService();
$count = 0;

foreach (App\Models\Sale::where('status', 'completed')->get() as $sale) {
    if (!$transactionService->isPosted(App\Models\Sale::class, $sale->id)) {
        try {
            $transactionService->postSale($sale);
            echo "Posted: {$sale->invoice_number}\n";
            $count++;
        } catch (Exception $e) {
            echo "Error: {$sale->invoice_number} - {$e->getMessage()}\n";
        }
    }
}

echo "Total posted: $count\n";
```

---

#### Issue 3: Observers Not Firing for New Sales

**Symptom:**
- NEW sales (created after the fix) don't appear in Cash Account Activity
- Journal entries are not created even for new sales

**Solution:**

1. **Check if observers are registered:**
```bash
cat app/Providers/AppServiceProvider.php | grep -A 10 "boot()"
```

You should see:
```php
Sale::observe(SaleObserver::class);
SaleReturn::observe(SaleReturnObserver::class);
GRN::observe(GrnObserver::class);
SupplierPayment::observe(SupplierPaymentObserver::class);
```

2. **Clear the application cache:**
```bash
php artisan optimize:clear
```

3. **Restart your web server/queue if using:**
```bash
# If using php artisan serve
# Stop with Ctrl+C and restart
php artisan serve

# If using queue
php artisan queue:restart
```

---

#### Issue 4: Account Exists But Wrong Balance

**Symptom:**
- Account 1110 exists
- Journal entries are being created
- But Daily Report shows wrong balance

**Solution:**

1. **Check if journal entries are posted (not just draft):**
```bash
php artisan tinker
```

```php
App\Models\JournalEntry::where('reference_type', App\Models\Sale::class)
    ->where('status', 'draft')
    ->count();
```

If there are draft entries, they won't affect the balance. This shouldn't happen with the observer, but if it does, manually post them.

2. **Recalculate account balances from journal entries:**

Create a command to recalculate:
```bash
php artisan tinker
```

```php
$cashAccount = App\Models\Account::where('account_code', '1110')->first();

$result = App\Models\JournalEntryLine::where('account_id', $cashAccount->id)
    ->whereHas('journalEntry', function ($q) {
        $q->where('status', 'posted');
    })
    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
    ->first();

$calculatedBalance = $result->total_debit - $result->total_credit;
$cashAccount->balance = $calculatedBalance;
$cashAccount->save();

echo "Updated cash balance to: Rs. $calculatedBalance\n";
```

---

### Step 3: Test the Fix

1. **Create a new test sale:**
   - Open a shift
   - Go to POS
   - Add a product
   - Complete the sale with CASH payment

2. **Check Daily Sales Report:**
   - Go to Reports → Daily Sales Report
   - Look for "Cash Account Activity" section
   - You should see:
     - Your sale as a cash inflow (green)
     - Updated balance

3. **Verify journal entry was created:**
```bash
php artisan tinker
```

```php
$lastSale = App\Models\Sale::latest()->first();
$entry = App\Models\JournalEntry::where('reference_type', App\Models\Sale::class)
    ->where('reference_id', $lastSale->id)
    ->first();

if ($entry) {
    echo "✓ Journal entry created: {$entry->entry_number}\n";
    echo "  Status: {$entry->status}\n";
    echo "  Amount: Rs. {$entry->total_debit}\n";
} else {
    echo "✗ No journal entry found for sale {$lastSale->invoice_number}\n";
}
```

---

## Quick Diagnostic Checklist

Run these commands to diagnose the issue:

```bash
# 1. Check if Account 1110 exists
php artisan tinker --execute="App\Models\Account::where('account_code', '1110')->first() ?? 'NOT FOUND'"

# 2. Check recent sales count
php artisan tinker --execute="echo App\Models\Sale::count() . ' sales found'"

# 3. Check journal entries count
php artisan tinker --execute="echo App\Models\JournalEntry::where('reference_type', App\Models\Sale::class)->count() . ' journal entries for sales'"

# 4. Check cash account transactions
php artisan tinker --execute="\$acc = App\Models\Account::where('account_code', '1110')->first(); if(\$acc) { \$count = App\Models\JournalEntryLine::where('account_id', \$acc->id)->count(); echo \$count . ' transactions on cash account'; } else { echo 'Cash account not found'; }"
```

---

## Understanding the Flow

For cash tracking to work, this flow must happen:

```
1. Sale Created in POS
   ↓
2. SaleObserver.created() triggered
   ↓
3. TransactionService.postSale() called
   ↓
4. JournalEntry created:
   DR Cash on Hand (1110)    Rs. X
   CR Sales Revenue (4110)        Rs. X
   ↓
5. JournalEntry posted (status='posted')
   ↓
6. Account.balance updated
   ↓
7. Daily Report queries JournalEntryLine
   ↓
8. Shows in Cash Account Activity ✓
```

If ANY step in this flow fails, cash tracking won't work.

---

## Prevention

To prevent this issue in the future:

1. **Always run seeders on new setups:**
```bash
php artisan db:seed
```

2. **Verify observers are registered** in `app/Providers/AppServiceProvider.php`

3. **Clear cache after code changes:**
```bash
php artisan optimize:clear
```

4. **Check logs regularly** for errors:
```bash
tail -f storage/logs/laravel.log
```

---

## Still Not Working?

If you've tried all the above and it's still not working:

1. **Enable debug logging** by adding this to `app/Observers/SaleObserver.php`:

```php
public function created(Sale $sale): void
{
    Log::info("SaleObserver triggered for sale: {$sale->invoice_number}");

    $this->cacheService->invalidateDashboardCache();

    if ($sale->status === 'completed' && $sale->payments()->count() > 0) {
        Log::info("Sale {$sale->invoice_number} is completed with payments, posting to accounting");

        try {
            if (!$this->transactionService->isPosted(Sale::class, $sale->id)) {
                Log::info("Sale {$sale->invoice_number} not yet posted, calling TransactionService");
                $this->transactionService->postSale($sale);
                Log::info("Sale {$sale->invoice_number} posted successfully");
            } else {
                Log::info("Sale {$sale->invoice_number} already posted, skipping");
            }
        } catch (\Exception $e) {
            Log::error("Failed to post sale: " . $e->getMessage(), [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    } else {
        Log::info("Sale {$sale->invoice_number} skipped: status={$sale->status}, payments=" . $sale->payments()->count());
    }
}
```

2. **Create a test sale and check the logs:**
```bash
tail -f storage/logs/laravel.log
```

3. **Share the log output** to debug further.

---

## Need More Help?

If you're still stuck, gather this information:

```bash
# Run the verification command and save output
php artisan cash:verify-setup > cash_verify_output.txt

# Check recent logs
tail -100 storage/logs/laravel.log > recent_logs.txt

# Check database state
php artisan tinker --execute="
echo 'Accounts: ' . App\Models\Account::count() . PHP_EOL;
echo 'Sales: ' . App\Models\Sale::count() . PHP_EOL;
echo 'Journal Entries: ' . App\Models\JournalEntry::count() . PHP_EOL;
echo 'Cash Account 1110 exists: ' . (App\Models\Account::where('account_code', '1110')->exists() ? 'YES' : 'NO') . PHP_EOL;
" > database_state.txt
```

Then review the three output files to identify the issue.
