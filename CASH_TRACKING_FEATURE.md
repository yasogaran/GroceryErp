# Cash Account Tracking Feature

## Overview

This feature adds comprehensive cash account tracking to the GroceryErp system. All cash transactions are now automatically tracked in the Cash on Hand account (Account Code: 1110) with real-time balance updates and detailed reporting.

## Implementation Summary

### What Was Already in Place

Your system already had a solid accounting foundation:

✅ **Chart of Accounts** with Cash on Hand (1110) account defined
✅ **Automatic Journal Entries** via TransactionService for all transactions
✅ **Double-Entry Bookkeeping** maintained throughout
✅ **Shift-Level Cash Tracking** with variance detection

### What Was Added

#### 1. **Automatic Account Balance Updates**
   - **File**: `app/Services/JournalEntryService.php` (lines 285-308)
   - **What**: Account balances are automatically updated when journal entries are posted or reversed
   - **How**: The `updateAccountBalance()` method applies debits/credits based on account type
   - **Impact**: Real-time accurate cash balance in the Account.balance field

#### 2. **Enhanced Daily Sales Report with Cash Activity**
   - **Files**:
     - `app/Livewire/Reports/DailySalesReport.php` (enhanced)
     - `resources/views/livewire/reports/daily-sales-report.blade.php` (enhanced)
   - **What**: Daily Sales Report now includes a dedicated "Cash Account Activity" section
   - **Features**:
     - Opening balance for the day
     - Total cash inflows (sales, receipts)
     - Total cash outflows (refunds, payments)
     - Closing balance for the day
     - Detailed transaction list with:
       - Time of transaction
       - Journal entry number
       - Description
       - Transaction type (Sale, Return, Payment, etc.)
       - Cash in/out amounts
       - Running balance
   - **Location**: Reports → Daily Sales Report

#### 3. **CashAccountService** (NEW)
   - **File**: `app/Services/CashAccountService.php`
   - **Purpose**: Centralized service for all cash account operations
   - **Methods**:
     - `getCurrentBalance()` - Get real-time cash balance
     - `getBalanceAsOf($date)` - Get cash balance on a specific date
     - `getDailyCashPosition($date)` - Get full daily cash summary
     - `getCashTransactions($startDate, $endDate)` - Get cash transactions for period
     - `getCashFlowSummary($startDate, $endDate)` - Get cash flow summary
     - `getCashAccountDetails()` - Get cash account information
   - **Usage**: Can be used by controllers, Livewire components, or other services

## How Cash Tracking Works

### Transaction Flow

1. **Transaction Occurs** (Sale, Purchase, Payment, Return, etc.)
2. **TransactionService** automatically creates a journal entry
   - For cash sales: Debit Cash (1110), Credit Sales Revenue (4110)
   - For cash refunds: Debit Sales Returns (4200), Credit Cash (1110)
   - For supplier payments: Debit Payables (2110), Credit Cash (1110)
3. **JournalEntry** is automatically posted
4. **Account Balance** is updated in real-time
5. **Reports** show current cash position

### Account Mapping

All cash transactions use the following mapping (TransactionService.php:416-433):

- **Cash payments** → Account 1110 (Cash on Hand)
- **Bank transfers** → Account 1210 (or specific bank account)
- **Credit sales** → Account 1310 (Customer Receivables)

## Using the Cash Tracking Features

### 1. View Daily Cash Activity

**Navigation**: Reports → Daily Sales Report

**What You'll See**:
- **Cash Summary Cards**:
  - Opening Balance (cash at start of day)
  - Cash Inflows (all cash received)
  - Cash Outflows (all cash paid out)
  - Closing Balance (cash at end of day)

- **Transaction Details Table**:
  - Time-ordered list of all cash transactions
  - Entry numbers for audit trail
  - Description and transaction type
  - Debit (cash in) and Credit (cash out)
  - Running balance after each transaction

**Use Case**: Daily reconciliation with shift cash counts

### 2. Check Current Cash Balance

**Method 1 - Using the Service (in code)**:
```php
use App\Services\CashAccountService;

$cashService = new CashAccountService();
$currentBalance = $cashService->getCurrentBalance();
```

**Method 2 - From Database**:
```php
$cashAccount = Account::where('account_code', '1110')->first();
$balance = $cashAccount->balance;
```

**Method 3 - From Reports**:
- Go to Reports → Trial Balance (shows all account balances)
- Go to Reports → Balance Sheet (shows cash in assets section)
- Go to Reports → Ledger Report (select Cash on Hand account)

### 3. Reconcile Cash with Shifts

**Daily Process**:
1. Open Daily Sales Report for the date
2. Check "Cash Account Activity" section
3. Compare with shift closing cash amounts
4. Investigate any variances

**Expected Reconciliation**:
```
Opening Balance (from previous day)
+ Cash Sales (from shifts)
- Cash Refunds
- Supplier Cash Payments
- Other Cash Expenses
= Closing Balance (should match physical cash count)
```

### 4. View Cash Flow Over Time

**Using CashAccountService**:
```php
$cashService = new CashAccountService();

// Weekly summary
$summary = $cashService->getCashFlowSummary('2025-11-01', '2025-11-07');

// Detailed transactions
$transactions = $cashService->getCashTransactions('2025-11-01', '2025-11-07');
```

**Manual Method**:
- Reports → Cash Book (enter date range)
- Reports → Ledger Report (select Cash on Hand account)

## Database Schema

### Accounts Table
```
account_code: '1110'
account_name: 'Cash on Hand'
account_type: 'asset'
balance: (automatically updated on each transaction)
is_system_account: true
```

### Journal Entries
All cash transactions create journal entries with:
- `entry_type`: 'sale', 'purchase', 'payment', 'return', 'manual'
- `reference_type`: Links back to Sale, GRN, SupplierPayment, etc.
- `status`: 'posted' (only posted entries affect account balances)

### Journal Entry Lines
Each line affects an account:
- `account_id`: Foreign key to accounts table
- `debit`: Amount that increases cash (for cash account)
- `credit`: Amount that decreases cash (for cash account)

## Key Files Modified/Created

### Created Files
- `app/Services/CashAccountService.php` - New service for cash operations
- `CASH_TRACKING_FEATURE.md` - This documentation

### Modified Files
- `app/Livewire/Reports/DailySalesReport.php` - Added cash activity tracking
- `resources/views/livewire/reports/daily-sales-report.blade.php` - Added cash activity UI

### Existing Files (No Changes - Already Working)
- `app/Services/JournalEntryService.php` - Already had account balance updates
- `app/Services/TransactionService.php` - Already mapped cash to account 1110
- `app/Services/FinancialReportService.php` - Already had cash book reporting

## Benefits of This Implementation

### ✅ Single Source of Truth
- All cash transactions flow through one accounting system
- No duplicate records or data sync issues
- Maintains accounting integrity

### ✅ Real-Time Accuracy
- Account balances always current
- Instant visibility into cash position
- No end-of-day batch processing needed

### ✅ Full Audit Trail
- Every cash movement has a journal entry
- Links back to source documents (sales, returns, payments)
- Complete transaction history

### ✅ Reconciliation Support
- Daily cash position matches shift cash counts
- Easy variance detection and investigation
- Detailed transaction breakdown

### ✅ Scalability
- Can easily add more reports/features
- Service-based architecture for reusability
- Clean separation of concerns

## Future Enhancements (Optional)

### Suggested Features
1. **Cash Position Dashboard Widget** - Real-time cash balance on home screen
2. **Cash Variance Report** - Track discrepancies over time
3. **Multi-Currency Support** - If needed for foreign currency
4. **Cash Transfer Between Accounts** - Move cash to/from bank
5. **Petty Cash Management** - Separate petty cash account with controls
6. **Cash Flow Statement** - Formal cash flow report (operating, investing, financing)

### How to Add Features
Use the `CashAccountService` as the foundation for any cash-related features:

```php
// Example: Dashboard widget
public function getCashDashboardWidget()
{
    $cashService = new CashAccountService();

    return [
        'current_balance' => $cashService->getCurrentBalance(),
        'today_position' => $cashService->getDailyCashPosition(now()->toDateString()),
        'account_details' => $cashService->getCashAccountDetails(),
    ];
}
```

## Testing the Feature

### Manual Testing Steps

1. **Create a Cash Sale**:
   - Go to POS
   - Add items and complete sale with cash payment
   - Note the invoice number and amount

2. **Check Daily Report**:
   - Go to Reports → Daily Sales Report
   - Verify cash inflow appears in "Cash Account Activity"
   - Check that balance increased by sale amount

3. **Process a Cash Refund**:
   - Go to Invoice History
   - Process a return with cash refund
   - Check Daily Report again - should show cash outflow

4. **Verify Account Balance**:
   - Go to Reports → Trial Balance
   - Find Cash on Hand (1110)
   - Balance should match Daily Report closing balance

5. **Check Shift Reconciliation**:
   - Close shift with actual cash count
   - Compare with Daily Report closing balance
   - Should reconcile (allowing for shift opening cash)

## Troubleshooting

### Issue: Cash balance seems incorrect
**Check**:
1. Go to Reports → Ledger Report, select Cash on Hand
2. Review all transactions - look for duplicates or missing entries
3. Check Journal Entries for status='posted' (drafts don't affect balance)

### Issue: Daily report shows no cash activity
**Check**:
1. Ensure date is correct
2. Verify Cash on Hand account exists (code 1110)
3. Check that sales are being posted to accounting (Sales should have journal entries)

### Issue: Balance doesn't match physical cash
**Reasons**:
1. Shift opening cash differs from previous closing
2. Cash removed for bank deposit (needs journal entry)
3. Unrecorded cash expense (needs journal entry)
4. Data entry error in sale amount

**Solution**: Create manual journal entry to correct if needed.

## Support & Maintenance

### Account Protection
The Cash on Hand account (1110) is marked as `is_system_account = true`, which prevents:
- Accidental deletion
- Unauthorized editing
- Maintain data integrity

### Data Integrity
- All journal entries are validated (debits must equal credits)
- Account balances updated in database transactions (atomic)
- Reversal entries maintain audit trail

### Performance
- Indexed queries for fast reporting
- Summary calculations use SQL aggregates
- Efficient date range filtering

## Conclusion

This cash tracking implementation leverages your existing accounting infrastructure while adding powerful reporting capabilities. The system now provides:

- ✅ Real-time cash balance tracking
- ✅ Detailed daily cash activity reports
- ✅ Full transaction audit trail
- ✅ Reconciliation support
- ✅ Foundation for future enhancements

All cash movements are automatically tracked through the double-entry bookkeeping system, ensuring accuracy and maintaining accounting integrity.
