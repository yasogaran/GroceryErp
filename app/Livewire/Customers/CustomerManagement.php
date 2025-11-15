<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedCustomerId = null;

    protected $queryString = ['search', 'statusFilter'];

    /**
     * Reset pagination when search is updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Open the create modal.
     */
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    /**
     * Open the edit modal.
     */
    public function openEditModal($customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showEditModal = true;
    }

    /**
     * Close all modals.
     */
    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedCustomerId = null;
    }

    /**
     * Listen for customer-created event.
     */
    #[On('customer-created')]
    public function customerCreated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Listen for customer-updated event.
     */
    #[On('customer-updated')]
    public function customerUpdated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Toggle customer active status.
     */
    public function toggleCustomerStatus($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $customer->is_active = !$customer->is_active;
        $customer->save();

        session()->flash('success', $customer->is_active ? 'Customer activated successfully.' : 'Customer deactivated successfully.');
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.customers.customer-management', [
            'customers' => $customers,
        ]);
    }
}
