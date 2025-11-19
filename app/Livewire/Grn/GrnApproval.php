<?php

namespace App\Livewire\Grn;

use App\Models\GRN;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GrnApproval extends Component
{
    public $grnId;
    public $grn;
    public $showApprovalModal = false;
    public $showPaymentModal = false;

    public function mount($id)
    {
        $this->grnId = $id;
        $this->loadGRN();
    }

    public function loadGRN()
    {
        $this->grn = GRN::with(['supplier', 'items.product', 'creator', 'approver'])
            ->findOrFail($this->grnId);
    }

    public function confirmApproval()
    {
        if ($this->grn->status === GRN::STATUS_APPROVED) {
            session()->flash('error', 'GRN is already approved');
            return;
        }

        $this->showApprovalModal = true;
    }

    public function approve()
    {
        try {
            $this->grn->approve(auth()->user());
            $this->loadGRN();
            $this->showApprovalModal = false;

            // Show payment modal after successful approval
            $this->showPaymentModal = true;

            session()->flash('success', 'GRN approved successfully. Stock has been updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error approving GRN: ' . $e->getMessage());
        }
    }

    public function openPaymentModal()
    {
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }

    public function cancelApproval()
    {
        $this->showApprovalModal = false;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.grn.grn-approval');
    }
}
