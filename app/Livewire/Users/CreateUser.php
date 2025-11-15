<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CreateUser extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'cashier';
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,manager,cashier,store_keeper,accountant'],
            'is_active' => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'User created successfully.');

        $this->dispatch('user-created');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.users.create-user', [
            'roles' => ['admin', 'manager', 'cashier', 'store_keeper', 'accountant'],
        ]);
    }
}
