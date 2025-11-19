<?php

namespace App\Livewire\Shifts;

use App\Models\Shift;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ShiftList extends Component
{
    use WithPagination;

    // Filters
    public $search = '';
    public $cashierFilter = '';
    public $statusFilter = 'all'; // all, open, closed
    public $verificationFilter = 'all'; // all, verified, unverified
    public $startDate = '';
    public $endDate = '';

    /**
     * Reset pagination when filters change
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCashierFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingVerificationFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    /**
     * Mount component
     */
    public function mount()
    {
        // Set default date range to last 30 days
        if (empty($this->startDate)) {
            $this->startDate = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->format('Y-m-d');
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Build query
        $query = Shift::with('cashier')
            ->orderBy('shift_start', 'desc');

        // Search by cashier name
        if (!empty($this->search)) {
            $query->whereHas('cashier', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by cashier
        if (!empty($this->cashierFilter)) {
            $query->where('cashier_id', $this->cashierFilter);
        }

        // Filter by status
        if ($this->statusFilter === 'open') {
            $query->open();
        } elseif ($this->statusFilter === 'closed') {
            $query->closed();
        }

        // Filter by verification status
        if ($this->verificationFilter === 'verified') {
            $query->where('is_verified', true);
        } elseif ($this->verificationFilter === 'unverified') {
            $query->where('is_verified', false);
        }

        // Filter by date range
        if (!empty($this->startDate)) {
            $query->whereDate('shift_start', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('shift_start', '<=', $this->endDate);
        }

        // Paginate results
        $shifts = $query->paginate(15);

        // Get all cashiers for filter dropdown
        $cashiers = User::whereHas('shifts')
            ->orderBy('name')
            ->get();

        return view('livewire.shifts.shift-list', [
            'shifts' => $shifts,
            'cashiers' => $cashiers,
        ]);
    }
}
