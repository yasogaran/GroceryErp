<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Services\FinancialReportService;
use Illuminate\Support\Facades\DB;

class DailySalesReport extends Component
{
    public $reportDate;

    public function mount()
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $sales = Sale::with(['items.product', 'customer', 'cashier'])
            ->whereDate('sale_date', $this->reportDate)
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'total_transactions' => $sales->count(),
            'cash_sales' => SalePayment::whereIn('sale_id', $sales->pluck('id'))
                ->where('payment_mode', 'cash')
                ->sum('amount'),
            'avg_transaction' => $sales->count() > 0
                ? $sales->sum('total_amount') / $sales->count()
                : 0,
        ];

        // Top selling products
        $topProducts = SaleItem::whereIn('sale_id', $sales->pluck('id'))
            ->select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total_price) as total_value'))
            ->groupBy('product_id')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->with('product')
            ->get();

        // Get cash account activity for the day
        $cashActivity = $this->getCashAccountActivity($this->reportDate);

        return view('livewire.reports.daily-sales-report', [
            'sales' => $sales,
            'summary' => $summary,
            'topProducts' => $topProducts,
            'cashActivity' => $cashActivity,
        ]);
    }

    /**
     * Get detailed cash account activity for a specific date
     */
    private function getCashAccountActivity(string $date): array
    {
        // Get Cash on Hand account (1110)
        $cashAccount = Account::where('account_code', '1110')->first();

        if (!$cashAccount) {
            return [
                'opening_balance' => 0,
                'closing_balance' => 0,
                'total_inflows' => 0,
                'total_outflows' => 0,
                'transactions' => [],
                'account_exists' => false,
            ];
        }

        // Get opening balance (balance at start of day)
        $openingBalance = $this->getCashBalanceBeforeDate($cashAccount->id, $date);

        // Get all cash transactions for the day
        $transactions = JournalEntryLine::where('account_id', $cashAccount->id)
            ->whereHas('journalEntry', function ($q) use ($date) {
                $q->where('status', 'posted')
                  ->whereDate('entry_date', $date);
            })
            ->with(['journalEntry'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Process transactions
        $processedTransactions = [];
        $totalInflows = 0;
        $totalOutflows = 0;
        $runningBalance = $openingBalance;

        foreach ($transactions as $line) {
            $debit = floatval($line->debit);
            $credit = floatval($line->credit);

            // For asset accounts (like Cash), debit increases, credit decreases
            $amount = $debit > 0 ? $debit : -$credit;
            $runningBalance += $amount;

            if ($debit > 0) {
                $totalInflows += $debit;
            }
            if ($credit > 0) {
                $totalOutflows += $credit;
            }

            $processedTransactions[] = [
                'time' => $line->journalEntry->created_at->format('H:i:s'),
                'entry_number' => $line->journalEntry->entry_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'reference_type' => $this->formatReferenceType($line->journalEntry->reference_type),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        return [
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'transactions' => $processedTransactions,
            'account_exists' => true,
            'account_name' => $cashAccount->account_name,
            'account_code' => $cashAccount->account_code,
        ];
    }

    /**
     * Get cash account balance before a specific date
     */
    private function getCashBalanceBeforeDate(int $accountId, string $date): float
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

        // For asset accounts, debit increases, credit decreases
        return $totalDebit - $totalCredit;
    }

    /**
     * Format reference type for display
     */
    private function formatReferenceType(?string $referenceType): string
    {
        if (!$referenceType) {
            return 'Manual Entry';
        }

        $parts = explode('\\', $referenceType);
        $className = end($parts);

        // Convert class names to readable format
        $readable = preg_replace('/([a-z])([A-Z])/', '$1 $2', $className);

        return $readable;
    }
}
