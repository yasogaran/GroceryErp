<?php

namespace App\Livewire\Shifts;

use App\Services\ShiftService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OpenShift extends Component
{
    public $openingCash = 0;

    protected $rules = [
        'openingCash' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $shiftService = app(ShiftService::class);

        // If user already has an open shift, redirect to POS
        if ($shiftService->hasOpenShift(auth()->user())) {
            return redirect()->route('pos.index');
        }
    }

    public function openShift()
    {
        $this->validate();

        try {
            $shiftService = app(ShiftService::class);
            $shift = $shiftService->openShift(auth()->user(), $this->openingCash);

            session()->flash('success', 'Shift opened successfully!');

            return redirect()->route('pos.index');
        } catch (\Exception $e) {
            $this->addError('openingCash', $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.shifts.open-shift');
    }
}
