<?php

namespace App\Livewire\Grn;

use App\Models\GRN;
use App\Models\SupplierPayment;
use App\Services\PaymentAllocationService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class GrnPaymentModal extends Component
{
    public $grn;
    public $showModal = false;

    // Payment fields
    public $payment_type = 'skip'; // 'full', 'partial', 'skip'
    public $payment_date;
    public $payment_amount = '';
    public $payment_mode = 'cash';
    public $bank_reference = '';
    public $reference_number = '';
    public $notes = '';

    protected function rules()
    {
        $rules = [];

        if ($this->payment_type !== 'skip') {
            $rules = [
                'payment_date' => 'required|date',
                'payment_amount' => 'required|numeric|min:0.01|max:' . $this->grn->total_amount,
                'payment_mode' => 'required|in:cash,bank_transfer',
                'bank_reference' => 'nullable|string|max:100',
                'reference_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ];
        }

        return $rules;
    }

    public function mount(GRN $grn)
    {
        $this->grn = $grn;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_amount = $grn->total_amount;
    }

    public function updatedPaymentType($value)
    {
        if ($value === 'full') {
            $this->payment_amount = $this->grn->total_amount;
        } elseif ($value === 'partial') {
            $this->payment_amount = '';
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->redirectRoute('grn.index');
    }

    public function recordPayment()
    {
        if ($this->payment_type === 'skip') {
            $this->closeModal();
            session()->flash('success', 'GRN approved successfully. Payment can be recorded later.');
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                // Create supplier payment
                $supplierPayment = SupplierPayment::create([
                    'supplier_id' => $this->grn->supplier_id,
                    'payment_date' => $this->payment_date,
                    'amount' => $this->payment_amount,
                    'payment_mode' => $this->payment_mode,
                    'bank_reference' => $this->bank_reference,
                    'reference_number' => $this->reference_number,
                    'notes' => $this->notes ?: "Payment for GRN {$this->grn->grn_number}",
                    'created_by' => auth()->id(),
                ]);

                // Allocate payment to this GRN
                $allocationService = app(PaymentAllocationService::class);
                $allocationService->allocatePayment($supplierPayment, [
                    $this->grn->id => $this->payment_amount
                ]);
            });

            $paymentTypeText = $this->payment_type === 'full' ? 'Full' : 'Partial';
            session()->flash('success', "{$paymentTypeText} payment of ₹" . number_format($this->payment_amount, 2) . " recorded successfully.");

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.grn.grn-payment-modal');
    }
}
