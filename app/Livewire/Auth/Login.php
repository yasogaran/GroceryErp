<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        // Rate limiting: 5 attempts per 15 minutes
        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]));
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Check if user is active
            if (!auth()->user()->is_active) {
                Auth::logout();
                session()->flash('error', 'Your account has been deactivated. Please contact an administrator.');
                return;
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);

            request()->session()->regenerate();

            // Log successful login
            \Log::info('[User: ' . auth()->user()->name . '] logged in from IP: ' . request()->ip());

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed login attempts
        RateLimiter::hit($throttleKey, 900); // 900 seconds = 15 minutes

        // Log failed login attempt
        \Log::warning('[User: ' . $this->email . '] failed login attempt from IP: ' . request()->ip());

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    #[Layout('components.layouts.guest')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
