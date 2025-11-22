<?php

namespace App\Livewire\ReportGeneration;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class JournalEntriesReport extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate;
    public $endDate;
    public $statusFilter = 'all'; // all, draft, posted, reversed
    public $accountFilter = '';
    public $sortBy = 'date';
    public $sortDirection = 'desc';
    public $perPage = 25;
    public $reportType = 'summary'; // summary, detailed

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'accountFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingAccountFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getJournalEntriesQuery()
    {
        $query = JournalEntry::with(['lines.account', 'createdBy'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->startDate, function ($q) {
                $q->whereDate('entry_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('entry_date', '<=', $this->endDate);
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->accountFilter, function ($q) {
                $q->whereHas('lines', function ($q) {
                    $q->where('account_id', $this->accountFilter);
                });
            });

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportToExcel()
    {
        $entries = $this->getJournalEntriesQuery()->get();

        if ($this->reportType === 'summary') {
            $data = $entries->map(function ($entry) {
                return [
                    'Date' => $entry->entry_date->format('Y-m-d'),
                    'Reference' => $entry->reference,
                    'Description' => $entry->description,
                    'Status' => ucfirst($entry->status),
                    'Total Debit' => number_format($entry->lines->sum('debit'), 2),
                    'Total Credit' => number_format($entry->lines->sum('credit'), 2),
                    'Created By' => $entry->createdBy?->name ?? 'N/A',
                    'Created At' => $entry->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            $headers = [
                'Date', 'Reference', 'Description', 'Status',
                'Total Debit', 'Total Credit', 'Created By', 'Created At'
            ];
        } else {
            // Detailed report with all lines
            $data = [];
            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    $data[] = [
                        'Date' => $entry->entry_date->format('Y-m-d'),
                        'Reference' => $entry->reference,
                        'Description' => $entry->description,
                        'Account Code' => $line->account?->account_code ?? 'N/A',
                        'Account Name' => $line->account?->account_name ?? 'N/A',
                        'Debit' => number_format($line->debit, 2),
                        'Credit' => number_format($line->credit, 2),
                        'Status' => ucfirst($entry->status),
                    ];
                }
            }

            $headers = [
                'Date', 'Reference', 'Description', 'Account Code',
                'Account Name', 'Debit', 'Credit', 'Status'
            ];
        }

        $exportService = new ReportExportService();
        return $exportService->exportToCSV($data, $headers, 'journal_entries_report_' . $this->reportType);
    }

    public function exportToPdf()
    {
        $entries = $this->getJournalEntriesQuery()->get();

        $stats = $this->calculateStats();

        return response()->view('reports.pdf.journal-entries', [
            'entries' => $entries,
            'stats' => $stats,
            'reportType' => $this->reportType,
            'generatedAt' => now(),
            'filters' => [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'status' => ucfirst($this->statusFilter),
                'account' => $this->accountFilter ? Account::find($this->accountFilter)?->account_name : 'All',
            ]
        ])->header('Content-Type', 'text/html');
    }

    private function calculateStats()
    {
        $entries = $this->getJournalEntriesQuery()->get();

        return [
            'total_entries' => $entries->count(),
            'posted_entries' => $entries->where('status', 'posted')->count(),
            'draft_entries' => $entries->where('status', 'draft')->count(),
            'reversed_entries' => $entries->where('status', 'reversed')->count(),
            'total_debits' => $entries->sum(function ($entry) {
                return $entry->lines->sum('debit');
            }),
            'total_credits' => $entries->sum(function ($entry) {
                return $entry->lines->sum('credit');
            }),
        ];
    }

    public function render()
    {
        $entries = $this->getJournalEntriesQuery()->paginate($this->perPage);
        $accounts = Account::orderBy('account_code')->get();
        $stats = $this->calculateStats();

        return view('livewire.report-generation.journal-entries-report', [
            'entries' => $entries,
            'accounts' => $accounts,
            'stats' => $stats,
        ]);
    }
}
