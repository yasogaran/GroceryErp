<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;

class EditUser extends Component
{
    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $is_active = true;

    public function mount($userId)
    {
        $this->userId = $userId;
        $user = User::findOrFail($userId);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->is_active = $user->is_active;
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->userId],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,manager,cashier,store_keeper,accountant'],
            'is_active' => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $user = User::findOrFail($this->userId);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
            'updated_by' => auth()->id(),
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        session()->flash('success', 'User updated successfully.');

        $this->dispatch('user-updated');
        $this->password = '';
        $this->password_confirmation = '';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.users.edit-user', [
            'roles' => ['admin', 'manager', 'cashier', 'store_keeper', 'accountant'],
        ]);
    }
}
