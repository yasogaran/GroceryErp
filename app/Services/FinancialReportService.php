<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FinancialReportService - Generates financial reports
 */
class FinancialReportService
{
    /**
     * Generate Trial Balance report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function generateTrialBalance(?string $startDate = null, ?string $endDate = null): array
    {
        $query = JournalEntry::query()
            ->where('status', 'posted');

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        }

        $entries = $query->with('lines.account')->get();

        // Calculate account balances
        $accountBalances = [];

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $accountId = $line->account_id;

                if (!isset($accountBalances[$accountId])) {
                    $accountBalances[$accountId] = [
                        'account' => $line->account,
                        'debit' => 0,
                        'credit' => 0,
                    ];
                }

                $accountBalances[$accountId]['debit'] = bcadd(
                    $accountBalances[$accountId]['debit'],
                    $line->debit,
                    2
                );

                $accountBalances[$accountId]['credit'] = bcadd(
                    $accountBalances[$accountId]['credit'],
                    $line->credit,
                    2
                );
            }
        }

        // Calculate totals
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accountBalances as $balance) {
            $totalDebit = bcadd($totalDebit, $balance['debit'], 2);
            $totalCredit = bcadd($totalCredit, $balance['credit'], 2);
        }

        // Sort by account type and code
        usort($accountBalances, function ($a, $b) {
            $typeOrder = ['asset' => 1, 'liability' => 2, 'equity' => 3, 'income' => 4, 'expense' => 5];
            $aType = $typeOrder[$a['account']->account_type] ?? 999;
            $bType = $typeOrder[$b['account']->account_type] ?? 999;

            if ($aType !== $bType) {
                return $aType <=> $bType;
            }

            return strcmp($a['account']->account_code, $b['account']->account_code);
        });

        return [
            'accounts' => $accountBalances,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => bccomp($totalDebit, $totalCredit, 2) === 0,
            'difference' => bcsub($totalDebit, $totalCredit, 2),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Generate Profit & Loss Statement
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generateProfitAndLoss(string $startDate, string $endDate): array
    {
        // Get income and expense accounts with their balances
        $incomeAccounts = $this->getAccountBalances('income', $startDate, $endDate);
        $expenseAccounts = $this->getAccountBalances('expense', $startDate, $endDate);

        // Calculate totals
        $totalIncome = 0;
        foreach ($incomeAccounts as $account) {
            // For income accounts, credit increases balance
            $balance = bcsub($account['credit'], $account['debit'], 2);
            $totalIncome = bcadd($totalIncome, $balance, 2);
        }

        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            // For expense accounts, debit increases balance
            $balance = bcsub($account['debit'], $account['credit'], 2);
            $totalExpenses = bcadd($totalExpenses, $balance, 2);
        }

        $netProfit = bcsub($totalIncome, $totalExpenses, 2);

        return [
            'income_accounts' => $incomeAccounts,
            'expense_accounts' => $expenseAccounts,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'is_profit' => bccomp($netProfit, '0', 2) >= 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Generate Balance Sheet
     *
     * @param string $asOfDate
     * @return array
     */
    public function generateBalanceSheet(string $asOfDate): array
    {
        // Get asset, liability, and equity accounts with their balances
        $assetAccounts = $this->getAccountBalances('asset', null, $asOfDate);
        $liabilityAccounts = $this->getAccountBalances('liability', null, $asOfDate);
        $equityAccounts = $this->getAccountBalances('equity', null, $asOfDate);

        // Calculate totals
        $totalAssets = 0;
        foreach ($assetAccounts as $account) {
            // For asset accounts, debit increases balance
            $balance = bcsub($account['debit'], $account['credit'], 2);
            $totalAssets = bcadd($totalAssets, $balance, 2);
        }

        $totalLiabilities = 0;
        foreach ($liabilityAccounts as $account) {
            // For liability accounts, credit increases balance
            $balance = bcsub($account['credit'], $account['debit'], 2);
            $totalLiabilities = bcadd($totalLiabilities, $balance, 2);
        }

        $totalEquity = 0;
        foreach ($equityAccounts as $account) {
            // For equity accounts, credit increases balance
            $balance = bcsub($account['credit'], $account['debit'], 2);
            $totalEquity = bcadd($totalEquity, $balance, 2);
        }

        // Add current year profit/loss to equity
        $yearStart = date('Y-01-01', strtotime($asOfDate));
        $plStatement = $this->generateProfitAndLoss($yearStart, $asOfDate);
        $currentYearPL = $plStatement['net_profit'];
        $totalEquity = bcadd($totalEquity, $currentYearPL, 2);

        $totalLiabilitiesAndEquity = bcadd($totalLiabilities, $totalEquity, 2);

        return [
            'asset_accounts' => $assetAccounts,
            'liability_accounts' => $liabilityAccounts,
            'equity_accounts' => $equityAccounts,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'current_year_pl' => $currentYearPL,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => bccomp($totalAssets, $totalLiabilitiesAndEquity, 2) === 0,
            'difference' => bcsub($totalAssets, $totalLiabilitiesAndEquity, 2),
            'as_of_date' => $asOfDate,
        ];
    }

    /**
     * Generate General Ledger for a specific account
     *
     * @param int $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function generateLedger(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $account = Account::findOrFail($accountId);

        $query = JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted');
                if ($startDate && $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate]);
                }
            })
            ->with(['journalEntry'])
            ->orderBy('created_at', 'asc');

        $lines = $query->get();

        // Calculate running balance
        $runningBalance = 0;
        $transactions = [];

        foreach ($lines as $line) {
            $debit = $line->debit;
            $credit = $line->credit;

            // Calculate running balance based on account type
            if (in_array($account->account_type, ['asset', 'expense'])) {
                // Debit increases, credit decreases
                $runningBalance = bcadd($runningBalance, $debit, 2);
                $runningBalance = bcsub($runningBalance, $credit, 2);
            } else {
                // Credit increases, debit decreases (liability, equity, income)
                $runningBalance = bcadd($runningBalance, $credit, 2);
                $runningBalance = bcsub($runningBalance, $debit, 2);
            }

            $transactions[] = [
                'date' => $line->journalEntry->entry_date,
                'entry_number' => $line->journalEntry->entry_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        return [
            'account' => $account,
            'transactions' => $transactions,
            'opening_balance' => 0, // TODO: Calculate from previous periods
            'closing_balance' => $runningBalance,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Generate Day Book (daily transaction summary)
     *
     * @param string $date
     * @return array
     */
    public function generateDayBook(string $date): array
    {
        $entries = JournalEntry::query()
            ->whereDate('entry_date', $date)
            ->where('status', 'posted')
            ->with(['lines.account', 'creator'])
            ->orderBy('created_at', 'asc')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $totalDebit = bcadd($totalDebit, $entry->total_debit, 2);
            $totalCredit = bcadd($totalCredit, $entry->total_credit, 2);
        }

        return [
            'entries' => $entries,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'date' => $date,
        ];
    }

    /**
     * Get account balances by type for a date range
     *
     * @param string $accountType
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getAccountBalances(string $accountType, ?string $startDate, ?string $endDate): array
    {
        $accounts = Account::where('account_type', $accountType)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            $query = JournalEntryLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted');
                    if ($startDate && $endDate) {
                        $q->whereBetween('entry_date', [$startDate, $endDate]);
                    } elseif ($endDate) {
                        $q->whereDate('entry_date', '<=', $endDate);
                    }
                });

            $totals = $query->selectRaw('
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
            ')->first();

            $debit = $totals->total_debit ?? 0;
            $credit = $totals->total_credit ?? 0;

            // Only include accounts with transactions
            if (bccomp($debit, '0', 2) > 0 || bccomp($credit, '0', 2) > 0) {
                $result[] = [
                    'account' => $account,
                    'debit' => $debit,
                    'credit' => $credit,
                ];
            }
        }

        return $result;
    }

    /**
     * Get cash book report
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $cashAccountCode Default: '1110' (Cash on Hand)
     * @return array
     */
    public function generateCashBook(string $startDate, string $endDate, string $cashAccountCode = '1110'): array
    {
        $cashAccount = Account::where('account_code', $cashAccountCode)->firstOrFail();
        return $this->generateLedger($cashAccount->id, $startDate, $endDate);
    }

    /**
     * Get bank book report
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $bankAccountId
     * @return array
     */
    public function generateBankBook(string $startDate, string $endDate, int $bankAccountId): array
    {
        return $this->generateLedger($bankAccountId, $startDate, $endDate);
    }
}
