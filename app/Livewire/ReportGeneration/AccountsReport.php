<?php

namespace App\Livewire\ReportGeneration;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AccountsReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $accountType = 'all'; // all, asset, liability, equity, income, expense
    public $accountFilter = '';
    public $reportType = 'summary'; // summary, detailed
    public $perPage = 50;

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'accountType' => ['except' => 'all'],
        'accountFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Default to current year
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingAccountType()
    {
        $this->resetPage();
    }

    public function updatingAccountFilter()
    {
        $this->resetPage();
    }

    public function getAccountsWithBalances()
    {
        $accounts = Account::query()
            ->when($this->accountType !== 'all', function ($q) {
                $q->where('account_type', $this->accountType);
            })
            ->when($this->accountFilter, function ($q) {
                $q->where('id', $this->accountFilter);
            })
            ->orderBy('account_code')
            ->get();

        foreach ($accounts as $account) {
            // Get opening balance (before start date)
            $openingBalance = $this->calculateBalance($account->id, null, $this->startDate, true);

            // Get transactions during period
            $periodDebits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $this->startDate)
                        ->whereDate('entry_date', '<=', $this->endDate);
                })
                ->sum('debit');

            $periodCredits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $this->startDate)
                        ->whereDate('entry_date', '<=', $this->endDate);
                })
                ->sum('credit');

            // Calculate closing balance
            $closingBalance = $this->calculateBalance($account->id, null, $this->endDate);

            $account->opening_balance = $openingBalance;
            $account->period_debits = $periodDebits;
            $account->period_credits = $periodCredits;
            $account->closing_balance = $closingBalance;
            $account->net_movement = bcsub(bcadd($periodDebits, '0', 2), bcadd($periodCredits, '0', 2), 2);
        }

        return $accounts;
    }

    private function calculateBalance($accountId, $startDate = null, $endDate = null, $beforeStartDate = false)
    {
        $query = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $beforeStartDate) {
                $q->where('status', 'posted');

                if ($beforeStartDate && $startDate) {
                    $q->whereDate('entry_date', '<', $startDate);
                } else {
                    if ($startDate) {
                        $q->whereDate('entry_date', '>=', $startDate);
                    }
                    if ($endDate) {
                        $q->whereDate('entry_date', '<=', $endDate);
                    }
                }
            });

        $debits = $query->sum('debit');
        $credits = $query->sum('credit');

        return bcsub(bcadd($debits, '0', 2), bcadd($credits, '0', 2), 2);
    }

    public function exportToExcel()
    {
        $accounts = $this->getAccountsWithBalances();

        if ($this->reportType === 'summary') {
            $data = $accounts->map(function ($account) {
                return [
                    'Code' => $account->account_code,
                    'Account Name' => $account->account_name,
                    'Type' => ucfirst($account->account_type),
                    'Opening Balance' => number_format($account->opening_balance, 2),
                    'Debits' => number_format($account->period_debits, 2),
                    'Credits' => number_format($account->period_credits, 2),
                    'Net Movement' => number_format($account->net_movement, 2),
                    'Closing Balance' => number_format($account->closing_balance, 2),
                ];
            })->toArray();

            $headers = [
                'Code', 'Account Name', 'Type', 'Opening Balance',
                'Debits', 'Credits', 'Net Movement', 'Closing Balance'
            ];
        } else {
            // Detailed report with all transactions
            $data = [];
            foreach ($accounts->where('period_debits', '>', 0)->merge($accounts->where('period_credits', '>', 0)) as $account) {
                $transactions = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) {
                        $q->where('status', 'posted')
                            ->whereDate('entry_date', '>=', $this->startDate)
                            ->whereDate('entry_date', '<=', $this->endDate);
                    })
                    ->with('journalEntry')
                    ->orderBy('id')
                    ->get();

                foreach ($transactions as $transaction) {
                    $data[] = [
                        'Date' => $transaction->journalEntry->entry_date->format('Y-m-d'),
                        'Account Code' => $account->account_code,
                        'Account Name' => $account->account_name,
                        'Reference' => $transaction->journalEntry->reference,
                        'Description' => $transaction->journalEntry->description,
                        'Debit' => number_format($transaction->debit, 2),
                        'Credit' => number_format($transaction->credit, 2),
                    ];
                }
            }

            $headers = [
                'Date', 'Account Code', 'Account Name', 'Reference',
                'Description', 'Debit', 'Credit'
            ];
        }

        $exportService = new ReportExportService();
        return $exportService->exportToCSV($data, $headers, 'accounts_report_' . $this->reportType);
    }

    public function exportToPdf()
    {
        $accounts = $this->getAccountsWithBalances();

        return response()->view('reports.pdf.accounts-report', [
            'accounts' => $accounts,
            'reportType' => $this->reportType,
            'generatedAt' => now(),
            'filters' => [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'accountType' => ucfirst(str_replace('_', ' ', $this->accountType)),
            ]
        ])->header('Content-Type', 'text/html');
    }

    public function render()
    {
        $accounts = $this->getAccountsWithBalances();

        // Calculate totals by type
        $totals = [
            'total_debits' => $accounts->sum('period_debits'),
            'total_credits' => $accounts->sum('period_credits'),
            'assets' => $accounts->where('account_type', 'asset')->sum('closing_balance'),
            'liabilities' => abs($accounts->where('account_type', 'liability')->sum('closing_balance')),
            'equity' => abs($accounts->where('account_type', 'equity')->sum('closing_balance')),
            'income' => abs($accounts->where('account_type', 'income')->sum('closing_balance')),
            'expenses' => $accounts->where('account_type', 'expense')->sum('closing_balance'),
        ];

        $allAccounts = Account::orderBy('account_code')->get();

        return view('livewire.report-generation.accounts-report', [
            'accounts' => $accounts,
            'allAccounts' => $allAccounts,
            'totals' => $totals,
        ]);
    }
}
