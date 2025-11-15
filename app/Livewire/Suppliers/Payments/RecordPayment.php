<?php

namespace App\Livewire\Suppliers\Payments;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Livewire\Component;

class RecordPayment extends Component
{
    public $supplier_id = '';
    public $payment_date;
    public $amount = '';
    public $payment_mode = 'cash';
    public $bank_reference = '';
    public $reference_number = '';
    public $notes = '';

    public $selectedSupplier = null;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'payment_date' => 'required|date',
        'amount' => 'required|numeric|min:0.01',
        'payment_mode' => 'required|in:cash,bank_transfer',
        'bank_reference' => 'nullable|string|max:100',
        'reference_number' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->payment_date = now()->format('Y-m-d');
    }

    public function updatedSupplierId($value)
    {
        if ($value) {
            $this->selectedSupplier = Supplier::find($value);
        } else {
            $this->selectedSupplier = null;
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // Validate amount doesn't exceed outstanding
            if ($this->selectedSupplier && $this->amount > $this->selectedSupplier->outstanding_balance) {
                session()->flash('error', "Payment amount (₹{$this->amount}) cannot exceed outstanding balance (₹{$this->selectedSupplier->outstanding_balance})");
                return;
            }

            SupplierPayment::create([
                'supplier_id' => $this->supplier_id,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'payment_mode' => $this->payment_mode,
                'bank_reference' => $this->bank_reference,
                'reference_number' => $this->reference_number,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            session()->flash('success', 'Payment recorded successfully');
            return redirect()->route('suppliers.payments.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $suppliers = Supplier::active()
            ->withOutstanding()
            ->orderBy('name')
            ->get();

        return view('livewire.suppliers.payments.record-payment', [
            'suppliers' => $suppliers,
        ])->layout('layouts.app');
    }
}
