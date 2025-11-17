<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    /**
     * Create a new backup.
     *
     * @param string $type 'manual' or 'automatic'
     * @param int|null $userId
     * @return Backup
     */
    public function createBackup(string $type = 'manual', ?int $userId = null): Backup
    {
        $backup = Backup::create([
            'filename' => $this->generateFilename(),
            'file_path' => '',
            'file_size' => 0,
            'backup_type' => $type,
            'status' => 'in_progress',
            'started_at' => now(),
            'created_by' => $userId ?? auth()->id(),
        ]);

        try {
            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $backupPath = $backupDir . '/' . $backup->filename;

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Cannot create backup file');
            }

            // Backup database
            $this->backupDatabase($zip);

            // Backup important files
            $this->backupFiles($zip);

            $zip->close();

            // Get file size
            $fileSize = File::size($backupPath);

            // Update backup record
            $backup->update([
                'file_path' => 'backups/' . $backup->filename,
                'file_size' => $fileSize,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Backup created successfully', [
                'backup_id' => $backup->id,
                'filename' => $backup->filename,
                'size' => $fileSize,
            ]);

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $backup->fresh();
    }

    /**
     * Backup database to ZIP file.
     *
     * @param ZipArchive $zip
     * @return void
     */
    protected function backupDatabase(ZipArchive $zip): void
    {
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $dumpFile = storage_path('app/backup-temp/database.sql');

        // Create temp directory if it doesn't exist
        if (!File::exists(dirname($dumpFile))) {
            File::makeDirectory(dirname($dumpFile), 0755, true);
        }

        // Create database dump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($databaseName),
            escapeshellarg($dumpFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // If mysqldump is not available, fallback to manual export
            $this->exportDatabaseManually($dumpFile);
        }

        // Add to ZIP
        $zip->addFile($dumpFile, 'database/database.sql');

        // Clean up temp file after adding to zip
        register_shutdown_function(function () use ($dumpFile) {
            if (File::exists($dumpFile)) {
                File::delete($dumpFile);
            }
        });
    }

    /**
     * Export database manually if mysqldump is not available.
     *
     * @param string $filename
     * @return void
     */
    protected function exportDatabaseManually(string $filename): void
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = config('database.connections.mysql.database');
        $tableKey = 'Tables_in_' . $databaseName;

        $sql = "-- Database Export\n";
        $sql .= "-- Generated: " . now() . "\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "\n\n-- Table: {$tableName}\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);

                    $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }

        File::put($filename, $sql);
    }

    /**
     * Backup important files to ZIP.
     *
     * @param ZipArchive $zip
     * @return void
     */
    protected function backupFiles(ZipArchive $zip): void
    {
        // Backup uploaded files (product images, etc.)
        $publicPath = public_path('storage');
        if (File::exists($publicPath)) {
            $this->addDirectoryToZip($zip, $publicPath, 'files/storage');
        }

        // Backup .env file
        if (File::exists(base_path('.env'))) {
            $zip->addFile(base_path('.env'), 'config/.env');
        }
    }

    /**
     * Add directory to ZIP recursively.
     *
     * @param ZipArchive $zip
     * @param string $directory
     * @param string $zipPath
     * @return void
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPath): void
    {
        if (!File::exists($directory)) {
            return;
        }

        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
            $zip->addFile($filePath, $relativePath);
        }
    }

    /**
     * Restore from backup.
     *
     * @param Backup $backup
     * @return bool
     */
    public function restoreBackup(Backup $backup): bool
    {
        try {
            if (!$backup->fileExists()) {
                throw new \Exception('Backup file not found');
            }

            // Create a safety backup before restore
            $this->createBackup('automatic', auth()->id());

            $backupPath = storage_path('app/' . $backup->file_path);
            $extractPath = storage_path('app/backup-restore');

            // Clean extract directory
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            File::makeDirectory($extractPath, 0755, true);

            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open($backupPath) !== true) {
                throw new \Exception('Cannot open backup file');
            }
            $zip->extractTo($extractPath);
            $zip->close();

            // Restore database
            $databaseFile = $extractPath . '/database/database.sql';
            if (File::exists($databaseFile)) {
                $this->restoreDatabase($databaseFile);
            }

            // Restore files
            $filesPath = $extractPath . '/files/storage';
            if (File::exists($filesPath)) {
                $publicStoragePath = public_path('storage');
                if (File::exists($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                }
                File::copyDirectory($filesPath, $publicStoragePath);
            }

            // Clean up extract directory
            File::deleteDirectory($extractPath);

            Log::info('Backup restored successfully', [
                'backup_id' => $backup->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Backup restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore database from SQL file.
     *
     * @param string $filename
     * @return void
     */
    protected function restoreDatabase(string $filename): void
    {
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Try using mysql command
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($databaseName),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback to manual import
            $sql = File::get($filename);
            DB::unprepared($sql);
        }
    }

    /**
     * Delete old backups based on retention days.
     *
     * @param int $retentionDays
     * @return int Number of backups deleted
     */
    public function deleteOldBackups(int $retentionDays = 30): int
    {
        $cutoffDate = now()->subDays($retentionDays);

        $oldBackups = Backup::where('created_at', '<', $cutoffDate)
            ->where('backup_type', 'automatic')
            ->get();

        $count = 0;
        foreach ($oldBackups as $backup) {
            $backup->forceDelete();
            $count++;
        }

        Log::info('Old backups cleaned up', [
            'count' => $count,
            'retention_days' => $retentionDays,
        ]);

        return $count;
    }

    /**
     * Generate unique filename for backup.
     *
     * @return string
     */
    protected function generateFilename(): string
    {
        return 'backup-' . now()->format('Y-m-d-His') . '-' . uniqid() . '.zip';
    }

    /**
     * Get total size of all backups.
     *
     * @return int Size in bytes
     */
    public function getTotalBackupSize(): int
    {
        return Backup::completed()->sum('file_size');
    }

    /**
     * Get backup statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_backups' => Backup::count(),
            'completed_backups' => Backup::completed()->count(),
            'failed_backups' => Backup::failed()->count(),
            'manual_backups' => Backup::manual()->count(),
            'automatic_backups' => Backup::automatic()->count(),
            'total_size' => $this->getTotalBackupSize(),
            'last_backup' => Backup::completed()->latest()->first(),
        ];
    }
}
