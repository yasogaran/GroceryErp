<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierLedger extends Component
{
    use WithPagination;

    public $supplierId;
    public $supplier;
    public $startDate = '';
    public $endDate = '';

    public function mount($id)
    {
        $this->supplierId = $id;
        $this->supplier = Supplier::findOrFail($id);

        // Set default date range (last 30 days)
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Get GRNs (approved only)
        $grns = $this->supplier->grns()
            ->where('status', 'approved')
            ->whereBetween('grn_date', [$this->startDate, $this->endDate])
            ->orderBy('grn_date', 'desc')
            ->get();

        // Get payments
        $payments = $this->supplier->payments()
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->orderBy('payment_date', 'desc')
            ->get();

        // Combine and sort by date
        $transactions = collect();

        foreach ($grns as $grn) {
            $transactions->push([
                'date' => $grn->grn_date,
                'type' => 'GRN',
                'reference' => $grn->grn_number,
                'debit' => $grn->total_amount,
                'credit' => 0,
                'details' => "Goods received - {$grn->items->count()} items",
            ]);
        }

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'Payment',
                'reference' => $payment->reference_number ?? '-',
                'debit' => 0,
                'credit' => $payment->amount,
                'details' => "Payment via {$payment->payment_mode}",
            ]);
        }

        $transactions = $transactions->sortByDesc('date')->values();

        return view('livewire.suppliers.supplier-ledger', [
            'transactions' => $transactions,
            'totalDebit' => $grns->sum('total_amount'),
            'totalCredit' => $payments->sum('amount'),
        ]);
    }
}
