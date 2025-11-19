<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;

/**
 * CashAccountService - Provides cash account tracking and reporting
 */
class CashAccountService
{
    const CASH_ACCOUNT_CODE = '1110'; // Cash on Hand

    /**
     * Get the cash account
     *
     * @return Account|null
     */
    public function getCashAccount(): ?Account
    {
        return Account::where('account_code', self::CASH_ACCOUNT_CODE)->first();
    }

    /**
     * Get current cash balance (real-time from account balance field)
     *
     * @return float
     */
    public function getCurrentBalance(): float
    {
        $account = $this->getCashAccount();
        return $account ? floatval($account->balance) : 0;
    }

    /**
     * Get cash balance as of a specific date
     *
     * @param string $asOfDate Date in Y-m-d format
     * @return float
     */
    public function getBalanceAsOf(string $asOfDate): float
    {
        $account = $this->getCashAccount();
        if (!$account) {
            return 0;
        }

        $result = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('status', 'posted')
                  ->whereDate('entry_date', '<=', $asOfDate);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = floatval($result->total_debit ?? 0);
        $totalCredit = floatval($result->total_credit ?? 0);

        // For asset accounts, debit increases, credit decreases
        return $totalDebit - $totalCredit;
    }

    /**
     * Get cash position summary for a specific date
     *
     * @param string $date Date in Y-m-d format
     * @return array
     */
    public function getDailyCashPosition(string $date): array
    {
        $account = $this->getCashAccount();

        if (!$account) {
            return [
                'date' => $date,
                'opening_balance' => 0,
                'closing_balance' => 0,
                'total_inflows' => 0,
                'total_outflows' => 0,
                'net_change' => 0,
                'transaction_count' => 0,
            ];
        }

        // Get balance before the date (opening balance)
        $openingBalance = $this->getBalanceBeforeDate($account->id, $date);

        // Get transactions for the day
        $transactions = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->where('status', 'posted')
                  ->whereDate('entry_date', $date);
            })
            ->selectRaw('
                COUNT(*) as transaction_count,
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
            ')
            ->first();

        $totalInflows = floatval($transactions->total_debit ?? 0);
        $totalOutflows = floatval($transactions->total_credit ?? 0);
        $transactionCount = intval($transactions->transaction_count ?? 0);

        $netChange = $totalInflows - $totalOutflows;
        $closingBalance = $openingBalance + $netChange;

        return [
            'date' => $date,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'net_change' => $netChange,
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * Get cash transactions for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getCashTransactions(string $startDate, string $endDate): Collection
    {
        $account = $this->getCashAccount();

        if (!$account) {
            return collect([]);
        }

        return JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->with(['journalEntry'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($line) {
                return [
                    'date' => $line->journalEntry->entry_date,
                    'time' => $line->journalEntry->created_at->format('H:i:s'),
                    'entry_number' => $line->journalEntry->entry_number,
                    'description' => $line->description ?? $line->journalEntry->description,
                    'entry_type' => $line->journalEntry->entry_type,
                    'reference_type' => $line->journalEntry->reference_type,
                    'reference_id' => $line->journalEntry->reference_id,
                    'debit' => floatval($line->debit),
                    'credit' => floatval($line->credit),
                    'amount' => floatval($line->debit) - floatval($line->credit),
                ];
            });
    }

    /**
     * Get cash flow summary for a period
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCashFlowSummary(string $startDate, string $endDate): array
    {
        $account = $this->getCashAccount();

        if (!$account) {
            return [
                'period_start' => $startDate,
                'period_end' => $endDate,
                'opening_balance' => 0,
                'closing_balance' => 0,
                'total_inflows' => 0,
                'total_outflows' => 0,
                'net_change' => 0,
            ];
        }

        $openingBalance = $this->getBalanceBeforeDate($account->id, $startDate);

        $result = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalInflows = floatval($result->total_debit ?? 0);
        $totalOutflows = floatval($result->total_credit ?? 0);
        $netChange = $totalInflows - $totalOutflows;

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $openingBalance + $netChange,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'net_change' => $netChange,
        ];
    }

    /**
     * Get cash balance before a specific date
     *
     * @param int $accountId
     * @param string $date
     * @return float
     */
    private function getBalanceBeforeDate(int $accountId, string $date): float
    {
        $result = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->where('status', 'posted')
                  ->whereDate('entry_date', '<', $date);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = floatval($result->total_debit ?? 0);
        $totalCredit = floatval($result->total_credit ?? 0);

        return $totalDebit - $totalCredit;
    }

    /**
     * Check if cash account exists
     *
     * @return bool
     */
    public function cashAccountExists(): bool
    {
        return $this->getCashAccount() !== null;
    }

    /**
     * Get cash account details
     *
     * @return array|null
     */
    public function getCashAccountDetails(): ?array
    {
        $account = $this->getCashAccount();

        if (!$account) {
            return null;
        }

        return [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'type' => $account->account_type,
            'current_balance' => floatval($account->balance),
            'is_active' => $account->is_active,
        ];
    }
}
