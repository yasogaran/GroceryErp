<?php

namespace App\Livewire\Shifts;

use App\Services\ShiftService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CloseShift extends Component
{
    public $shift;
    public $closingCash = 0;
    public $varianceNotes = '';
    public $verified = false;

    public $calculatedVariance = 0;
    public $expectedCash = 0;

    protected $rules = [
        'closingCash' => 'required|numeric|min:0',
        'verified' => 'required|accepted',
        'varianceNotes' => 'nullable|string',
    ];

    protected $messages = [
        'verified.accepted' => 'You must verify the cash count to close the shift.',
    ];

    public function mount()
    {
        $shiftService = app(ShiftService::class);
        $this->shift = $shiftService->getCurrentShift(auth()->user());

        if (!$this->shift) {
            return redirect()->route('shift.open')
                ->with('error', 'No active shift found.');
        }

        $this->calculateExpectedCash();
    }

    public function updatedClosingCash()
    {
        $this->calculateVariance();
    }

    private function calculateExpectedCash()
    {
        $this->expectedCash = $this->shift->opening_cash + $this->shift->total_cash_sales;
    }

    private function calculateVariance()
    {
        $this->calculatedVariance = $this->closingCash - $this->expectedCash;
    }

    public function closeShift()
    {
        $this->validate();

        try {
            $shiftService = app(ShiftService::class);
            $shiftService->closeShift($this->shift, $this->closingCash, $this->varianceNotes);

            session()->flash('success', 'Shift closed successfully!');

            // Log out the user
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login');
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.shifts.close-shift');
    }
}
