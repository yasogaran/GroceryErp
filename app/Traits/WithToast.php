<?php

namespace App\Traits;

trait WithToast
{
    /**
     * Show a success toast notification
     */
    protected function toastSuccess($message, $duration = 5000)
    {
        $this->dispatch('showToast', type: 'success', message: $message, duration: $duration);
    }

    /**
     * Show an error toast notification
     */
    protected function toastError($message, $duration = 7000)
    {
        $this->dispatch('showToast', type: 'error', message: $message, duration: $duration);
    }

    /**
     * Show a warning toast notification
     */
    protected function toastWarning($message, $duration = 6000)
    {
        $this->dispatch('showToast', type: 'warning', message: $message, duration: $duration);
    }

    /**
     * Show an info toast notification
     */
    protected function toastInfo($message, $duration = 5000)
    {
        $this->dispatch('showToast', type: 'info', message: $message, duration: $duration);
    }

    /**
     * Show validation errors as a toast notification
     */
    protected function toastValidationErrors(\Illuminate\Validation\ValidationException $e)
    {
        $errors = $e->validator->errors()->all();
        $errorMessage = count($errors) > 1
            ? 'Please fix the validation errors: ' . implode(', ', array_slice($errors, 0, 2)) . (count($errors) > 2 ? '...' : '')
            : $errors[0];

        $this->toastError($errorMessage);
    }
}
