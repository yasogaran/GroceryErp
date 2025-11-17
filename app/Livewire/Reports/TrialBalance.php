<?php

namespace App\Livewire\Reports;

use App\Services\FinancialReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TrialBalance extends Component
{
    public $startDate;
    public $endDate;
    public $reportData = null;

    public function mount()
    {
        $this->startDate = now()->startOfYear()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function generateReport()
    {
        $service = new FinancialReportService();
        $this->reportData = $service->generateTrialBalance($this->startDate, $this->endDate);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.reports.trial-balance');
    }
}
