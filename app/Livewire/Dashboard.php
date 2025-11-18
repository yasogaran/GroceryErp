<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    public $dashboardComponent;

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $user = auth()->user();

        // Determine which dashboard component to load based on role
        $this->dashboardComponent = match($user->role) {
            'admin' => 'dashboards.admin-dashboard',
            'manager' => 'dashboards.manager-dashboard',
            'accountant' => 'dashboards.accountant-dashboard',
            'cashier' => 'dashboards.cashier-dashboard',
            'store_keeper' => 'dashboards.store-keeper-dashboard',
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
