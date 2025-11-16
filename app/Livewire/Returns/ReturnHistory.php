<?php

namespace App\Livewire\Returns;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SaleReturn;
use Livewire\Attributes\Layout;

class ReturnHistory extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $searchTerm = '';
    public $selectedReturn = null;
    public $showDetailModal = false;

    public function mount()
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function viewDetails($returnId)
    {
        $this->selectedReturn = SaleReturn::with([
            'originalSale.items.product',
            'customer',
            'items.product',
            'items.saleItem',
            'creator',
            'bankAccount'
        ])->find($returnId);

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedReturn = null;
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = SaleReturn::with(['originalSale', 'customer', 'items.product', 'creator']);

        if ($this->dateFrom) {
            $query->whereDate('return_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('return_date', '<=', $this->dateTo);
        }

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('return_number', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('originalSale', function($sq) {
                      $sq->where('invoice_number', 'like', '%' . $this->searchTerm . '%');
                  })
                  ->orWhereHas('customer', function($cq) {
                      $cq->where('name', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        $returns = $query->latest('return_date')->paginate(20);

        // Calculate summary statistics
        $allReturns = SaleReturn::query();
        if ($this->dateFrom) {
            $allReturns->whereDate('return_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $allReturns->whereDate('return_date', '<=', $this->dateTo);
        }

        $summary = [
            'total_returns' => $allReturns->count(),
            'total_refund_amount' => $allReturns->sum('total_refund_amount'),
            'cash_refunds' => $allReturns->where('refund_mode', 'cash')->sum('total_refund_amount'),
            'bank_refunds' => $allReturns->where('refund_mode', 'bank_transfer')->sum('total_refund_amount'),
        ];

        return view('livewire.returns.return-history', [
            'returns' => $returns,
            'summary' => $summary,
        ]);
    }
}
