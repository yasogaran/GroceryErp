<?php

namespace App\Livewire\Reports;

use App\Services\FinancialReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BalanceSheet extends Component
{
    public $asOfDate;
    public $reportData = null;

    public function mount()
    {
        $this->asOfDate = now()->toDateString();
    }

    public function generateReport()
    {
        $this->validate([
            'asOfDate' => 'required|date',
        ]);

        $service = new FinancialReportService();
        $this->reportData = $service->generateBalanceSheet($this->asOfDate);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.reports.balance-sheet');
    }
}
