<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Services\FinancialReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LedgerReport extends Component
{
    public $accountId = '';
    public $startDate;
    public $endDate;
    public $reportData = null;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function generateReport()
    {
        $this->validate([
            'accountId' => 'required|exists:accounts,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $service = new FinancialReportService();
        $this->reportData = $service->generateLedger($this->accountId, $this->startDate, $this->endDate);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $accounts = Account::active()->orderBy('account_code')->get();
        return view('livewire.reports.ledger-report', compact('accounts'));
    }
}
