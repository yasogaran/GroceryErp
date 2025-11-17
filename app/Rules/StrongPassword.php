<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Minimum 8 characters
        if (strlen($value) < 8) {
            $fail('The :attribute must be at least 8 characters.');
        }

        // Must contain at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
        }

        // Must contain at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
        }

        // Must contain at least one number
        if (!preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');
        }

        // Must contain at least one special character
        if (!preg_match('/[@$!%*?&#]/', $value)) {
            $fail('The :attribute must contain at least one special character (@$!%*?&#).');
        }

        // Check against common passwords
        $commonPasswords = [
            'password', 'Password1', 'Password123', '12345678',
            'Abc12345', 'Admin123', 'Welcome1', 'Qwerty12'
        ];

        if (in_array($value, $commonPasswords)) {
            $fail('The :attribute is too common. Please choose a stronger password.');
        }
    }
}
