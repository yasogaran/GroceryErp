<?php

namespace App\Livewire\Backups;

use App\Models\Backup;
use App\Services\BackupService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class BackupManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $showUploadModal = false;
    public $showRestoreConfirmModal = false;
    public $uploadedBackupFile;
    public $backupToRestore;
    public $isCreatingBackup = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function mount()
    {
        // Check if user is admin
        if (!auth()->user() || !in_array(auth()->user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatus']);
        $this->resetPage();
    }

    public function createBackup()
    {
        try {
            $this->isCreatingBackup = true;

            $backupService = new BackupService();
            $backup = $backupService->createBackup('manual', auth()->id());

            session()->flash('success', 'Backup created successfully!');
            $this->dispatch('backup-created');

        } catch (\Exception $e) {
            session()->flash('error', 'Backup creation failed: ' . $e->getMessage());
        } finally {
            $this->isCreatingBackup = false;
        }
    }

    public function downloadBackup($backupId)
    {
        $backup = Backup::findOrFail($backupId);

        if (!$backup->fileExists()) {
            session()->flash('error', 'Backup file not found.');
            return;
        }

        return Storage::download($backup->file_path, $backup->filename);
    }

    public function confirmRestore($backupId)
    {
        $this->backupToRestore = Backup::findOrFail($backupId);
        $this->showRestoreConfirmModal = true;
    }

    public function restoreBackup()
    {
        try {
            if (!$this->backupToRestore) {
                throw new \Exception('No backup selected for restore.');
            }

            $backupService = new BackupService();
            $backupService->restoreBackup($this->backupToRestore);

            session()->flash('success', 'Backup restored successfully! Please refresh the page.');
            $this->showRestoreConfirmModal = false;
            $this->backupToRestore = null;

        } catch (\Exception $e) {
            session()->flash('error', 'Backup restore failed: ' . $e->getMessage());
            $this->showRestoreConfirmModal = false;
        }
    }

    public function deleteBackup($backupId)
    {
        try {
            $backup = Backup::findOrFail($backupId);

            // Only allow deleting manual backups or old automatic backups
            if ($backup->backup_type === 'manual' || auth()->user()->role === 'admin') {
                $backup->delete(); // Soft delete
                session()->flash('success', 'Backup deleted successfully.');
            } else {
                session()->flash('error', 'You can only delete manual backups.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    public function forceDeleteBackup($backupId)
    {
        try {
            // Only admin can permanently delete
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }

            $backup = Backup::withTrashed()->findOrFail($backupId);
            $backup->forceDelete(); // This will also delete the file

            session()->flash('success', 'Backup permanently deleted.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to permanently delete backup: ' . $e->getMessage());
        }
    }

    public function getStatistics()
    {
        $backupService = new BackupService();
        return $backupService->getStatistics();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Backup::query()->with('creator');

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('filename', 'like', "%{$this->search}%")
                  ->orWhere('status', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterType) {
            $query->where('backup_type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $backups = $query->latest()->paginate(15);
        $statistics = $this->getStatistics();

        return view('livewire.backups.backup-management', [
            'backups' => $backups,
            'statistics' => $statistics,
        ]);
    }
}
