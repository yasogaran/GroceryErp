<?php

namespace App\Livewire\GRN;

use App\Models\GRN;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class GRNList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all'; // all, draft, approved
    public $paymentStatusFilter = 'all'; // all, unpaid, partially_paid, fully_paid
    public $supplierFilter = '';
    public $startDate = '';
    public $endDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'paymentStatusFilter' => ['except' => 'all'],
        'supplierFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Set default date range (last 30 days)
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = GRN::query()->with(['supplier', 'creator', 'approver', 'items']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('grn_number', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', function ($sq) {
                        $sq->where('name', 'like', "%{$this->search}%");
                    });
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply payment status filter
        if ($this->paymentStatusFilter !== 'all') {
            $query->where('payment_status', $this->paymentStatusFilter);
        }

        // Apply supplier filter
        if ($this->supplierFilter) {
            $query->where('supplier_id', $this->supplierFilter);
        }

        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('grn_date', [$this->startDate, $this->endDate]);
        }

        $grns = $query->orderBy('grn_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get suppliers for filter dropdown
        $suppliers = \App\Models\Supplier::active()->orderBy('name')->get();

        return view('livewire.grn.grn-list', [
            'grns' => $grns,
            'suppliers' => $suppliers,
        ]);
    }
}
