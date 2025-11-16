<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AccountManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedAccountId = null;

    protected $queryString = ['search', 'typeFilter', 'statusFilter'];

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
    public function openEditModal($accountId)
    {
        $this->selectedAccountId = $accountId;
        $this->showEditModal = true;
    }

    /**
     * Close all modals.
     */
    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedAccountId = null;
    }

    /**
     * Listen for account-created event.
     */
    #[On('account-created')]
    public function accountCreated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Listen for account-updated event.
     */
    #[On('account-updated')]
    public function accountUpdated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Toggle account active status.
     */
    public function toggleAccountStatus($accountId)
    {
        $account = Account::findOrFail($accountId);

        // System accounts can be toggled
        $account->is_active = !$account->is_active;
        $account->save();

        session()->flash('success', $account->is_active ? 'Account activated successfully.' : 'Account deactivated successfully.');
    }

    /**
     * Delete an account.
     */
    public function deleteAccount($accountId)
    {
        $account = Account::findOrFail($accountId);

        // Check if account can be deleted
        if (!$account->canBeDeleted()) {
            session()->flash('error', 'Cannot delete this account. It is a system account, has child accounts, or has transactions.');
            return;
        }

        $account->delete();
        session()->flash('success', 'Account deleted successfully.');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $accounts = Account::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('account_name', 'like', '%' . $this->search . '%')
                      ->orWhere('account_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter !== '', function ($query) {
                $query->where('account_type', $this->typeFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->with(['parent', 'children'])
            ->orderBy('account_type', 'asc')
            ->orderBy('account_code', 'asc')
            ->paginate(15);

        $accountTypes = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'income' => 'Income',
            'expense' => 'Expense',
            'equity' => 'Equity',
        ];

        return view('livewire.accounts.account-management', [
            'accounts' => $accounts,
            'accountTypes' => $accountTypes,
        ]);
    }
}
