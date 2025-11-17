<?php

namespace App\Livewire\Reports;

use App\Services\FinancialReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DayBook extends Component
{
    public $date;
    public $reportData = null;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function generateReport()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        $service = new FinancialReportService();
        $this->reportData = $service->generateDayBook($this->date);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.reports.day-book');
    }
}
