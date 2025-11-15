<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ActivityLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $filterUser = '';
    public $filterAction = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $perPage = 100;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterUser' => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Check if user is admin
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access. Admin only.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterUser()
    {
        $this->resetPage();
    }

    public function updatingFilterAction()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterUser', 'filterAction', 'filterDateFrom', 'filterDateTo']);
        $this->resetPage();
    }

    public function getActivityLogs()
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return collect([]);
        }

        // Read the log file
        $logContent = file_get_contents($logPath);

        // Split into individual log entries
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?local\.INFO: (.*?)(?=\[\d{4}-\d{2}-\d{2}|$)/s';
        preg_match_all($pattern, $logContent, $matches, PREG_SET_ORDER);

        $logs = collect($matches)->map(function ($match) {
            $timestamp = $match[1];
            $message = trim($match[2]);

            // Parse the log message
            // Format: [User: {name}] {action} {model} #{id} at {timestamp}
            if (preg_match('/\[User: (.*?)\] (created|updated|deleted) (\w+) #(.*?) at/', $message, $parts)) {
                return [
                    'timestamp' => $timestamp,
                    'user' => $parts[1],
                    'action' => $parts[2],
                    'module' => $parts[3],
                    'model_id' => $parts[4],
                    'description' => $message,
                    'raw' => $message,
                ];
            }

            // Parse authentication events
            if (preg_match('/\[User: (.*?)\] (logged in|logged out|failed login attempt)/', $message, $parts)) {
                return [
                    'timestamp' => $timestamp,
                    'user' => $parts[1],
                    'action' => $parts[2],
                    'module' => 'Authentication',
                    'model_id' => 'N/A',
                    'description' => $message,
                    'raw' => $message,
                ];
            }

            // If it doesn't match our format, check if it contains user activity keywords
            if (Str::contains($message, ['[User:', 'created', 'updated', 'deleted'])) {
                return [
                    'timestamp' => $timestamp,
                    'user' => 'Unknown',
                    'action' => 'unknown',
                    'module' => 'System',
                    'model_id' => 'N/A',
                    'description' => $message,
                    'raw' => $message,
                ];
            }

            return null;
        })->filter(); // Remove null entries

        // Reverse to show latest first
        $logs = $logs->reverse()->values();

        // Apply filters
        if ($this->search) {
            $logs = $logs->filter(function ($log) {
                return Str::contains(strtolower($log['description']), strtolower($this->search)) ||
                       Str::contains(strtolower($log['user']), strtolower($this->search)) ||
                       Str::contains(strtolower($log['module']), strtolower($this->search));
            });
        }

        if ($this->filterUser) {
            $logs = $logs->filter(function ($log) {
                return Str::contains(strtolower($log['user']), strtolower($this->filterUser));
            });
        }

        if ($this->filterAction) {
            $logs = $logs->filter(function ($log) {
                return $log['action'] === $this->filterAction;
            });
        }

        if ($this->filterDateFrom) {
            $logs = $logs->filter(function ($log) {
                return $log['timestamp'] >= $this->filterDateFrom;
            });
        }

        if ($this->filterDateTo) {
            $logs = $logs->filter(function ($log) {
                return $log['timestamp'] <= $this->filterDateTo . ' 23:59:59';
            });
        }

        return $logs;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $logs = $this->getActivityLogs();

        // Manual pagination
        $currentPage = $this->getPage();
        $total = $logs->count();
        $logs = $logs->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return view('livewire.activity-logs', [
            'logs' => $logs,
            'total' => $total,
            'currentPage' => $currentPage,
            'lastPage' => ceil($total / $this->perPage),
        ]);
    }
}
