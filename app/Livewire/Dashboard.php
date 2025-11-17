<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.dashboard');
    }
}
