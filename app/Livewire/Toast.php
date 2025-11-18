<?php

namespace App\Livewire;

use Livewire\Component;

class Toast extends Component
{
    public $messages = [];
    public $enableSound = true;

    protected $listeners = [
        'showToast' => 'addMessage',
        'showSuccess' => 'addSuccessMessage',
        'showError' => 'addErrorMessage',
        'showWarning' => 'addWarningMessage',
        'showInfo' => 'addInfoMessage',
    ];

    /**
     * Add a message to the toast queue
     */
    public function addMessage($type, $message, $duration = 5000)
    {
        $id = uniqid();

        $this->messages[] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'duration' => $duration,
        ];

        // Remove old messages (keep only last 5)
        if (count($this->messages) > 5) {
            array_shift($this->messages);
        }

        // Auto-remove message after duration
        $this->dispatch('toast-added', id: $id, duration: $duration);
    }

    /**
     * Add a success message
     */
    public function addSuccessMessage($message, $duration = 5000)
    {
        $this->addMessage('success', $message, $duration);
    }

    /**
     * Add an error message
     */
    public function addErrorMessage($message, $duration = 7000)
    {
        $this->addMessage('error', $message, $duration);
    }

    /**
     * Add a warning message
     */
    public function addWarningMessage($message, $duration = 6000)
    {
        $this->addMessage('warning', $message, $duration);
    }

    /**
     * Add an info message
     */
    public function addInfoMessage($message, $duration = 5000)
    {
        $this->addMessage('info', $message, $duration);
    }

    /**
     * Remove a message by ID
     */
    public function removeMessage($id)
    {
        $this->messages = array_filter($this->messages, function($msg) use ($id) {
            return $msg['id'] !== $id;
        });

        // Re-index array
        $this->messages = array_values($this->messages);
    }

    /**
     * Mount component with session flash messages
     */
    public function mount()
    {
        // Check for session flash messages
        if (session()->has('success')) {
            $this->addSuccessMessage(session('success'));
        }

        if (session()->has('error')) {
            $this->addErrorMessage(session('error'));
        }

        if (session()->has('warning')) {
            $this->addWarningMessage(session('warning'));
        }

        if (session()->has('info')) {
            $this->addInfoMessage(session('info'));
        }
    }

    public function render()
    {
        return view('livewire.toast');
    }
}
