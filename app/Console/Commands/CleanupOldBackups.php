<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use App\Models\Setting;
use Illuminate\Console\Command;

class CleanupOldBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup {--days= : Number of days to retain backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old backups based on retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = $this->option('days') ?? Setting::get('backup_retention_days', 30);

        $this->info('Cleaning up backups older than ' . $retentionDays . ' days...');

        try {
            $backupService = new BackupService();
            $deletedCount = $backupService->deleteOldBackups($retentionDays);

            $this->info('Successfully deleted ' . $deletedCount . ' old backup(s).');

            return 0;

        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
