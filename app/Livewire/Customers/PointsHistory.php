<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Customer;
use App\Models\PointTransaction;
use App\Services\LoyaltyService;

class PointsHistory extends Component
{
    use WithPagination;

    public $customerId;
    public $customer;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = Customer::findOrFail($customerId);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $transactions = PointTransaction::where('customer_id', $this->customerId)
            ->with(['creator', 'sale'])
            ->latest()
            ->paginate(20);

        $summary = app(LoyaltyService::class)->getPointsSummary($this->customer);

        return view('livewire.customers.points-history', [
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }
}
