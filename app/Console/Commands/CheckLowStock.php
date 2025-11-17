<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low {--notify : Send email notifications to admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock products and create alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock products...');

        try {
            $alertService = new AlertService();
            $stats = $alertService->checkLowStock();

            $this->info('Low stock check completed!');
            $this->line('');
            $this->line('Statistics:');
            $this->line('  Products checked: ' . $stats['checked']);
            $this->line('  Low stock products: ' . $stats['low_stock']);
            $this->line('  Out of stock products: ' . $stats['critical_stock']);
            $this->line('  New alerts created: ' . $stats['alerts_created']);

            if ($stats['critical_stock'] > 0) {
                $this->warn('⚠ WARNING: ' . $stats['critical_stock'] . ' product(s) are OUT OF STOCK!');
            }

            // TODO: Send email notifications if --notify flag is provided
            if ($this->option('notify')) {
                $this->info('Email notifications would be sent here (not implemented yet).');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Low stock check failed: ' . $e->getMessage());
            return 1;
        }
    }
}
