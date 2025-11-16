<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;

class EditAccount extends Component
{
    public $accountId;
    public $account_code = '';
    public $account_name = '';
    public $account_type = '';
    public $parent_id = null;
    public $is_active = true;
    public $is_system_account = false;

    /**
     * Mount the component with account data.
     */
    public function mount($accountId)
    {
        $this->accountId = $accountId;
        $account = Account::findOrFail($accountId);

        $this->account_code = $account->account_code;
        $this->account_name = $account->account_name;
        $this->account_type = $account->account_type;
        $this->parent_id = $account->parent_id;
        $this->is_active = $account->is_active;
        $this->is_system_account = $account->is_system_account;
    }

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'account_code' => ['required', 'string', 'max:50', 'unique:accounts,account_code,' . $this->accountId],
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
     * Update the account.
     */
    public function update()
    {
        $account = Account::findOrFail($this->accountId);

        // Check if account can be edited
        if (!$account->canBeEdited()) {
            session()->flash('error', 'System accounts cannot be edited.');
            return;
        }

        $validated = $this->validate();

        $account->update([
            'account_code' => strtoupper($validated['account_code']),
            'account_name' => $validated['account_name'],
            'account_type' => $validated['account_type'],
            'parent_id' => $validated['parent_id'],
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'Account updated successfully.');

        $this->dispatch('account-updated');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $parentAccounts = Account::whereNull('parent_id')
            ->where('id', '!=', $this->accountId)
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

        return view('livewire.accounts.edit-account', [
            'parentAccounts' => $parentAccounts,
            'accountTypes' => $accountTypes,
        ]);
    }
}
