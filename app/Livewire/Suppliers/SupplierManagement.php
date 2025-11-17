<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all'; // all, active, inactive
    public $outstandingFilter = 'all'; // all, with_outstanding, no_outstanding

    public $deleteConfirmId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'outstandingFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingOutstandingFilter()
    {
        $this->resetPage();
    }

    public function toggleActive($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();

        session()->flash('success', 'Supplier status updated successfully');
    }

    public function confirmDelete($supplierId)
    {
        $this->deleteConfirmId = $supplierId;
    }

    public function cancelDelete()
    {
        $this->deleteConfirmId = null;
    }

    public function delete()
    {
        $supplier = Supplier::findOrFail($this->deleteConfirmId);

        if (!$supplier->canDelete()) {
            session()->flash('error', 'Cannot delete supplier with existing GRNs or outstanding balance');
            $this->deleteConfirmId = null;
            return;
        }

        $supplier->delete();
        session()->flash('success', 'Supplier deleted successfully');
        $this->deleteConfirmId = null;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Supplier::query()->with(['grns', 'payments']);

        // Apply search filter
        if ($this->search) {
            $query->search($this->search);
        }

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        // Apply outstanding filter
        if ($this->outstandingFilter === 'with_outstanding') {
            $query->where('outstanding_balance', '>', 0);
        } elseif ($this->outstandingFilter === 'no_outstanding') {
            $query->where('outstanding_balance', '=', 0);
        }

        $suppliers = $query->orderBy('name')->paginate(15);

        return view('livewire.suppliers.supplier-management', [
            'suppliers' => $suppliers,
        ]);
    }
}
