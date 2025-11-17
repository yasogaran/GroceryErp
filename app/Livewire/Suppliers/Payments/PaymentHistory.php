<?php

namespace App\Livewire\Suppliers\Payments;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentHistory extends Component
{
    use WithPagination;

    public $supplierFilter = '';
    public $paymentModeFilter = 'all';
    public $startDate = '';
    public $endDate = '';

    protected $queryString = [
        'supplierFilter' => ['except' => ''],
        'paymentModeFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Set default date range (last 30 days)
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentModeFilter()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = SupplierPayment::query()->with(['supplier', 'creator']);

        // Apply supplier filter
        if ($this->supplierFilter) {
            $query->where('supplier_id', $this->supplierFilter);
        }

        // Apply payment mode filter
        if ($this->paymentModeFilter !== 'all') {
            $query->where('payment_mode', $this->paymentModeFilter);
        }

        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payment_date', [$this->startDate, $this->endDate]);
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get suppliers for filter dropdown
        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.suppliers.payments.payment-history', [
            'payments' => $payments,
            'suppliers' => $suppliers,
        ]);
    }
}
