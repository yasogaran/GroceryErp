<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;

class Login extends Component
{
    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $password = '';

    public $remember = false;

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Check if user is active
            if (!auth()->user()->is_active) {
                $userName = auth()->user()->name;
                Auth::logout();

                // Log failed login attempt (inactive account)
                Log::info("[User: {$userName}] failed login attempt (account inactive) at " . now()->toDateTimeString(), [
                    'user_name' => $userName,
                    'action' => 'failed login attempt',
                    'ip_address' => request()->ip(),
                    'reason' => 'Account inactive',
                    'timestamp' => now()->toDateTimeString(),
                ]);

                session()->flash('error', 'Your account has been deactivated. Please contact an administrator.');
                return;
            }

            // Log successful login
            $user = auth()->user();
            Log::info("[User: {$user->name}] logged in at " . now()->toDateTimeString(), [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'logged in',
                'ip_address' => request()->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Log failed login attempt
        Log::warning("[User: {$this->email}] failed login attempt at " . now()->toDateTimeString(), [
            'user_name' => $this->email,
            'action' => 'failed login attempt',
            'ip_address' => request()->ip(),
            'reason' => 'Invalid credentials',
            'timestamp' => now()->toDateTimeString(),
        ]);

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
