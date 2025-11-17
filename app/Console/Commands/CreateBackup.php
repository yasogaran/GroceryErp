<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create {--type=automatic : The type of backup (manual or automatic)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database and important files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        if (!in_array($type, ['manual', 'automatic'])) {
            $this->error('Invalid backup type. Use "manual" or "automatic".');
            return 1;
        }

        $this->info('Starting backup process...');

        try {
            $backupService = new BackupService();
            $backup = $backupService->createBackup($type);

            $this->info('Backup created successfully!');
            $this->line('Filename: ' . $backup->filename);
            $this->line('Size: ' . $backup->formatted_size);
            $this->line('Status: ' . $backup->status);

            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
