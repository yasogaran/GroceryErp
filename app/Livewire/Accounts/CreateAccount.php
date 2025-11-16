<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;

class CreateAccount extends Component
{
    public $account_code = '';
    public $account_name = '';
    public $account_type = 'asset';
    public $parent_id = null;
    public $is_active = true;

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'account_code' => ['required', 'string', 'max:50', 'unique:accounts,account_code'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'in:asset,liability,income,expense,equity'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected $messages = [
        'account_code.required' => 'Account code is required.',
        'account_code.unique' => 'An account with this code already exists.',
        'account_name.required' => 'Account name is required.',
        'account_type.required' => 'Account type is required.',
        'parent_id.exists' => 'The selected parent account does not exist.',
    ];

    /**
     * Save the new account.
     */
    public function save()
    {
        $validated = $this->validate();

        Account::create([
            'account_code' => strtoupper($validated['account_code']),
            'account_name' => $validated['account_name'],
            'account_type' => $validated['account_type'],
            'parent_id' => $validated['parent_id'],
            'is_system_account' => false,
            'balance' => 0,
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'Account created successfully.');

        $this->dispatch('account-created');
        $this->reset();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $parentAccounts = Account::whereNull('parent_id')
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get()
            ->groupBy('account_type');

        $accountTypes = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'income' => 'Income',
            'expense' => 'Expense',
            'equity' => 'Equity',
        ];

        return view('livewire.accounts.create-account', [
            'parentAccounts' => $parentAccounts,
            'accountTypes' => $accountTypes,
        ]);
    }
}
