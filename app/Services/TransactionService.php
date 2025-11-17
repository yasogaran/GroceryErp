<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Sale;
use App\Models\GRN;
use App\Models\SaleReturn;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TransactionService - Handles automatic journal entry creation for business transactions
 * Implements double-entry bookkeeping for all financial operations
 */
class TransactionService
{
    protected JournalEntryService $journalService;

    public function __construct()
    {
        $this->journalService = new JournalEntryService();
    }

    /**
     * Post a sale transaction to accounting
     *
     * Accounting Entry:
     * DR Cash/Bank/Customer Receivable (payment mode)
     * CR Sales Revenue
     * CR Sales Discount (if any)
     *
     * @param Sale $sale
     * @return void
     * @throws \Exception
     */
    public function postSale(Sale $sale): void
    {
        DB::beginTransaction();

        try {
            $lines = [];
            $totalAmount = $sale->total_amount;
            $description = "Sale #{$sale->invoice_number}";

            // Handle multiple payment methods
            foreach ($sale->payments as $payment) {
                $debitAccount = $this->getPaymentAccount($payment->payment_mode, $payment->bank_account_id);

                $lines[] = [
                    'account_id' => $debitAccount,
                    'description' => $description . " - {$payment->payment_mode}",
                    'debit' => $payment->amount,
                    'credit' => 0,
                ];
            }

            // Credit: Sales Revenue (4110)
            $salesAccount = $this->getAccountByCode('4110');
            $lines[] = [
                'account_id' => $salesAccount->id,
                'description' => $description,
                'debit' => 0,
                'credit' => $totalAmount,
            ];

            // Create journal entry
            $this->journalService->createEntry([
                'entry_date' => $sale->created_at->toDateString(),
                'description' => $description,
                'entry_type' => 'sale',
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'lines' => $lines,
                'status' => 'draft',
            ]);

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post sale to accounting: " . $e->getMessage(), [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Post a purchase (GRN) transaction to accounting
     *
     * Accounting Entry:
     * DR Inventory (Stock in Hand)
     * CR Supplier Payables
     *
     * @param GRN $grn
     * @return void
     * @throws \Exception
     */
    public function postPurchase(GRN $grn): void
    {
        DB::beginTransaction();

        try {
            $description = "Purchase GRN #{$grn->grn_number}";

            // DR: Stock in Hand (1410)
            $inventoryAccount = $this->getAccountByCode('1410');

            // CR: Supplier Payables (2110)
            $payablesAccount = $this->getAccountByCode('2110');

            $this->journalService->createSimpleEntry(
                entryDate: $grn->approved_at?->toDateString() ?? now()->toDateString(),
                debitAccountId: $inventoryAccount->id,
                creditAccountId: $payablesAccount->id,
                amount: $grn->total_amount,
                description: $description,
                entryType: 'purchase',
                referenceType: GRN::class,
                referenceId: $grn->id
            );

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', GRN::class)
                ->where('reference_id', $grn->id)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post purchase to accounting: " . $e->getMessage(), [
                'grn_id' => $grn->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Post a supplier payment transaction to accounting
     *
     * Accounting Entry:
     * DR Supplier Payables
     * CR Cash/Bank (payment mode)
     *
     * @param SupplierPayment $payment
     * @return void
     * @throws \Exception
     */
    public function postSupplierPayment(SupplierPayment $payment): void
    {
        DB::beginTransaction();

        try {
            $description = "Payment to Supplier - Ref: {$payment->reference_number}";

            // DR: Supplier Payables (2110)
            $payablesAccount = $this->getAccountByCode('2110');

            // CR: Cash/Bank based on payment mode
            $creditAccount = $this->getPaymentAccount($payment->payment_mode, $payment->bank_account_id ?? null);

            $this->journalService->createSimpleEntry(
                entryDate: $payment->payment_date->toDateString(),
                debitAccountId: $payablesAccount->id,
                creditAccountId: $creditAccount,
                amount: $payment->amount,
                description: $description,
                entryType: 'payment',
                referenceType: SupplierPayment::class,
                referenceId: $payment->id
            );

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', SupplierPayment::class)
                ->where('reference_id', $payment->id)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post supplier payment to accounting: " . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Post a sales return transaction to accounting
     *
     * Accounting Entry:
     * DR Sales Returns & Allowances
     * CR Cash/Bank (refund mode)
     *
     * @param SaleReturn $return
     * @return void
     * @throws \Exception
     */
    public function postSaleReturn(SaleReturn $return): void
    {
        DB::beginTransaction();

        try {
            $description = "Sale Return #{$return->return_number}";

            // DR: Sales Returns & Allowances (4200)
            $returnsAccount = $this->getAccountByCode('4200');

            // CR: Cash/Bank based on refund mode
            $creditAccount = $this->getPaymentAccount($return->refund_mode, $return->bank_account_id);

            $this->journalService->createSimpleEntry(
                entryDate: $return->created_at->toDateString(),
                debitAccountId: $returnsAccount->id,
                creditAccountId: $creditAccount,
                amount: $return->total_refund_amount,
                description: $description,
                entryType: 'return',
                referenceType: SaleReturn::class,
                referenceId: $return->id
            );

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', SaleReturn::class)
                ->where('reference_id', $return->id)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post sale return to accounting: " . $e->getMessage(), [
                'return_id' => $return->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Post stock write-off to accounting
     *
     * Accounting Entry:
     * DR Stock Write-offs Expense
     * CR Inventory (Stock in Hand)
     *
     * @param float $amount
     * @param string $description
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     * @throws \Exception
     */
    public function postStockWriteOff(
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        DB::beginTransaction();

        try {
            // DR: Stock Write-offs (5130)
            $writeOffAccount = $this->getAccountByCode('5130');

            // CR: Stock in Hand (1410)
            $inventoryAccount = $this->getAccountByCode('1410');

            $this->journalService->createSimpleEntry(
                entryDate: now()->toDateString(),
                debitAccountId: $writeOffAccount->id,
                creditAccountId: $inventoryAccount->id,
                amount: (string)$amount,
                description: "Stock Write-off - " . $description,
                entryType: 'adjustment',
                referenceType: $referenceType,
                referenceId: $referenceId
            );

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post stock write-off to accounting: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Post expense transaction to accounting
     *
     * Accounting Entry:
     * DR Expense Account
     * CR Cash/Bank
     *
     * @param int $expenseAccountId
     * @param float $amount
     * @param string $description
     * @param string $paymentMode
     * @param int|null $bankAccountId
     * @param string $date
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     * @throws \Exception
     */
    public function postExpense(
        int $expenseAccountId,
        float $amount,
        string $description,
        string $paymentMode,
        ?int $bankAccountId = null,
        ?string $date = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        DB::beginTransaction();

        try {
            // DR: Expense Account (provided)
            // CR: Cash/Bank based on payment mode
            $creditAccount = $this->getPaymentAccount($paymentMode, $bankAccountId);

            $this->journalService->createSimpleEntry(
                entryDate: $date ?? now()->toDateString(),
                debitAccountId: $expenseAccountId,
                creditAccountId: $creditAccount,
                amount: (string)$amount,
                description: $description,
                entryType: 'manual',
                referenceType: $referenceType,
                referenceId: $referenceId
            );

            // Auto-post the entry
            $entry = \App\Models\JournalEntry::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->latest()
                ->first();

            if ($entry) {
                $this->journalService->postEntry($entry);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post expense to accounting: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get account by code
     *
     * @param string $code
     * @return Account
     * @throws \Exception
     */
    private function getAccountByCode(string $code): Account
    {
        $account = Account::where('account_code', $code)->first();

        if (!$account) {
            throw new \Exception("Account with code {$code} not found. Please run account seeder.");
        }

        return $account;
    }

    /**
     * Get appropriate payment account based on payment mode
     *
     * @param string $paymentMode
     * @param int|null $bankAccountId
     * @return int Account ID
     * @throws \Exception
     */
    private function getPaymentAccount(string $paymentMode, ?int $bankAccountId = null): int
    {
        if ($paymentMode === 'cash') {
            // Cash on Hand (1110)
            return $this->getAccountByCode('1110')->id;
        } elseif ($paymentMode === 'bank_transfer' || $paymentMode === 'bank') {
            // Use specific bank account if provided, otherwise default to Bank Account 1 (1210)
            if ($bankAccountId) {
                return $bankAccountId;
            }
            return $this->getAccountByCode('1210')->id;
        } elseif ($paymentMode === 'credit') {
            // Customer Receivables (1310)
            return $this->getAccountByCode('1310')->id;
        }

        throw new \Exception("Unknown payment mode: {$paymentMode}");
    }

    /**
     * Check if a transaction has been posted to accounting
     *
     * @param string $referenceType
     * @param int $referenceId
     * @return bool
     */
    public function isPosted(string $referenceType, int $referenceId): bool
    {
        return \App\Models\JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', 'posted')
            ->exists();
    }

    /**
     * Get journal entry for a transaction
     *
     * @param string $referenceType
     * @param int $referenceId
     * @return \App\Models\JournalEntry|null
     */
    public function getEntry(string $referenceType, int $referenceId): ?\App\Models\JournalEntry
    {
        return \App\Models\JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->with('lines.account')
            ->first();
    }
}
