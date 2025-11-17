<?php

namespace App\Livewire\JournalEntries;

use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class JournalEntryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $selectedEntryId = null;
    public $showReverseModal = false;
    public $reversalReason = '';

    protected $queryString = ['search', 'statusFilter', 'typeFilter', 'startDate', 'endDate'];

    /**
     * Reset pagination when search is updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Mount the component.
     */
    public function mount()
    {
        // Set default date range to current month
        if (!$this->startDate) {
            $this->startDate = now()->startOfMonth()->toDateString();
        }
        if (!$this->endDate) {
            $this->endDate = now()->endOfMonth()->toDateString();
        }
    }

    /**
     * Open create modal.
     */
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    /**
     * Open edit modal.
     */
    public function openEditModal($entryId)
    {
        $this->selectedEntryId = $entryId;
        $this->showEditModal = true;
    }

    /**
     * Open view modal.
     */
    public function openViewModal($entryId)
    {
        $this->selectedEntryId = $entryId;
        $this->showViewModal = true;
    }

    /**
     * Open reverse modal.
     */
    public function openReverseModal($entryId)
    {
        $this->selectedEntryId = $entryId;
        $this->showReverseModal = true;
        $this->reversalReason = '';
    }

    /**
     * Close all modals.
     */
    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showReverseModal = false;
        $this->selectedEntryId = null;
        $this->reversalReason = '';
    }

    /**
     * Post a journal entry.
     */
    public function postEntry($entryId)
    {
        try {
            $entry = JournalEntry::findOrFail($entryId);
            $service = new JournalEntryService();
            $service->postEntry($entry);

            session()->flash('success', 'Journal entry posted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to post entry: ' . $e->getMessage());
        }
    }

    /**
     * Reverse a journal entry.
     */
    public function reverseEntry()
    {
        $this->validate([
            'reversalReason' => 'required|string|min:10',
        ]);

        try {
            $entry = JournalEntry::findOrFail($this->selectedEntryId);
            $service = new JournalEntryService();
            $service->reverseEntry($entry, $this->reversalReason);

            session()->flash('success', 'Journal entry reversed successfully.');
            $this->closeModals();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reverse entry: ' . $e->getMessage());
        }
    }

    /**
     * Delete a journal entry.
     */
    public function deleteEntry($entryId)
    {
        try {
            $entry = JournalEntry::findOrFail($entryId);
            $service = new JournalEntryService();
            $service->deleteEntry($entry);

            session()->flash('success', 'Journal entry deleted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete entry: ' . $e->getMessage());
        }
    }

    /**
     * Listen for entry-created event.
     */
    #[On('entry-created')]
    public function entryCreated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Listen for entry-updated event.
     */
    #[On('entry-updated')]
    public function entryUpdated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[Layout('components.layouts.app')]
    public function render()
    {
        $entries = JournalEntry::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('entry_number', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter !== '', function ($query) {
                $query->where('entry_type', $this->typeFilter);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('entry_date', [$this->startDate, $this->endDate]);
            })
            ->with(['creator', 'lines.account'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $entryTypes = [
            'manual' => 'Manual',
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'payment' => 'Payment',
            'return' => 'Return',
            'adjustment' => 'Adjustment',
        ];

        $statusTypes = [
            'draft' => 'Draft',
            'posted' => 'Posted',
            'reversed' => 'Reversed',
        ];

        return view('livewire.journal-entries.journal-entry-management', [
            'entries' => $entries,
            'entryTypes' => $entryTypes,
            'statusTypes' => $statusTypes,
        ]);
    }
}
