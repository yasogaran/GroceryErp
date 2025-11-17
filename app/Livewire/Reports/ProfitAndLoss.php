<?php

namespace App\Livewire\Reports;

use App\Services\FinancialReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProfitAndLoss extends Component
{
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
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $service = new FinancialReportService();
        $this->reportData = $service->generateProfitAndLoss($this->startDate, $this->endDate);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.reports.profit-and-loss');
    }
}
