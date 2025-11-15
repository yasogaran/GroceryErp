<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedUserId = null;

    protected $queryString = ['search', 'roleFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->updated_by = auth()->id();
        $user->save();

        session()->flash('success', $user->is_active ? 'User activated successfully.' : 'User deactivated successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        // Prevent deleting if user has created or updated other users
        if ($user->createdUsers()->exists() || $user->updatedUsers()->exists()) {
            session()->flash('error', 'Cannot delete user with audit trail references.');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully.');
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->showEditModal = true;
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedUserId = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->with(['creator', 'updater'])
            ->latest()
            ->paginate(10);

        return view('livewire.users.user-management', [
            'users' => $users,
            'roles' => ['admin', 'manager', 'cashier', 'store_keeper', 'accountant'],
        ]);
    }
}
