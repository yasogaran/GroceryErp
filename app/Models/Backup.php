<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'file_path',
        'file_size',
        'backup_type',
        'status',
        'error_message',
        'started_at',
        'completed_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the backup.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include completed backups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed backups.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include manual backups.
     */
    public function scopeManual($query)
    {
        return $query->where('backup_type', 'manual');
    }

    /**
     * Scope a query to only include automatic backups.
     */
    public function scopeAutomatic($query)
    {
        return $query->where('backup_type', 'automatic');
    }

    /**
     * Get the formatted file size.
     *
     * @return string
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the duration of the backup process.
     *
     * @return string|null
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $seconds = $this->completed_at->diffInSeconds($this->started_at);

        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes . 'm ' . $remainingSeconds . 's';
    }

    /**
     * Check if the backup file exists.
     *
     * @return bool
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete the backup file from storage.
     *
     * @return bool
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }

        return false;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a backup is deleted, also delete the file
        static::deleting(function ($backup) {
            if ($backup->isForceDeleting()) {
                $backup->deleteFile();
            }
        });
    }
}
