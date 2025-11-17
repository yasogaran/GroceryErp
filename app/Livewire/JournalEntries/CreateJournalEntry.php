<?php

namespace App\Livewire\JournalEntries;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Livewire\Component;

class CreateJournalEntry extends Component
{
    public $entry_date;
    public $description = '';
    public $entry_type = 'manual';
    public $lines = [];
    public $totalDebit = 0;
    public $totalCredit = 0;
    public $autoPost = false;

    /**
     * Mount the component.
     */
    public function mount()
    {
        $this->entry_date = now()->toDateString();
        $this->addLine(); // Start with one line
        $this->addLine(); // And another
    }

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:1000'],
            'entry_type' => ['required', 'in:manual,sale,purchase,payment,return,adjustment'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected $messages = [
        'entry_date.required' => 'Entry date is required.',
        'description.required' => 'Description is required.',
        'lines.required' => 'At least 2 lines are required.',
        'lines.min' => 'At least 2 lines are required.',
        'lines.*.account_id.required' => 'Account is required for all lines.',
        'lines.*.account_id.exists' => 'Selected account does not exist.',
        'lines.*.debit.required' => 'Debit amount is required.',
        'lines.*.credit.required' => 'Credit amount is required.',
    ];

    /**
     * Add a new line.
     */
    public function addLine()
    {
        $this->lines[] = [
            'account_id' => '',
            'description' => '',
            'debit' => '0.00',
            'credit' => '0.00',
        ];
    }

    /**
     * Remove a line.
     */
    public function removeLine($index)
    {
        if (count($this->lines) > 2) {
            unset($this->lines[$index]);
            $this->lines = array_values($this->lines); // Re-index array
            $this->calculateTotals();
        }
    }

    /**
     * Calculate totals when lines change.
     */
    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'lines.')) {
            $this->calculateTotals();
        }
    }

    /**
     * Calculate total debits and credits.
     */
    public function calculateTotals()
    {
        $this->totalDebit = 0;
        $this->totalCredit = 0;

        foreach ($this->lines as $line) {
            $this->totalDebit = bcadd($this->totalDebit, $line['debit'] ?? 0, 2);
            $this->totalCredit = bcadd($this->totalCredit, $line['credit'] ?? 0, 2);
        }
    }

    /**
     * Check if entry is balanced.
     */
    public function isBalanced(): bool
    {
        return bccomp($this->totalDebit, $this->totalCredit, 2) === 0
            && bccomp($this->totalDebit, '0', 2) > 0;
    }

    /**
     * Save the journal entry.
     */
    public function save($andPost = false)
    {
        $validated = $this->validate();

        // Additional validation - check balance
        if (!$this->isBalanced()) {
            session()->flash('error', 'Journal entry is not balanced. Total debits must equal total credits.');
            return;
        }

        // Validate each line has either debit or credit (not both)
        foreach ($this->lines as $index => $line) {
            $debit = floatval($line['debit']);
            $credit = floatval($line['credit']);

            if ($debit > 0 && $credit > 0) {
                session()->flash('error', "Line " . ($index + 1) . " cannot have both debit and credit amounts.");
                return;
            }

            if ($debit == 0 && $credit == 0) {
                session()->flash('error', "Line " . ($index + 1) . " must have either a debit or credit amount.");
                return;
            }
        }

        try {
            $service = new JournalEntryService();
            $entry = $service->createEntry($validated);

            // Post if requested and user wants to
            if ($andPost) {
                $service->postEntry($entry);
                session()->flash('success', 'Journal entry created and posted successfully.');
            } else {
                session()->flash('success', 'Journal entry created successfully as draft.');
            }

            $this->dispatch('entry-created');
            $this->reset();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create entry: ' . $e->getMessage());
        }
    }

    /**
     * Save and post immediately.
     */
    public function saveAndPost()
    {
        $this->save(true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $accounts = Account::active()
            ->orderBy('account_code')
            ->get()
            ->groupBy('account_type');

        $entryTypes = [
            'manual' => 'Manual Entry',
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'payment' => 'Payment',
            'return' => 'Return',
            'adjustment' => 'Adjustment',
        ];

        return view('livewire.journal-entries.create-journal-entry', [
            'accounts' => $accounts,
            'entryTypes' => $entryTypes,
        ]);
    }
}
