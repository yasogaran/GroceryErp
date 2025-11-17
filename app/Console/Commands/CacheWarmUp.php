<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheWarmUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application caches with critical data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Warming up application caches...');

        try {
            $cacheService = new CacheService();
            $cacheService->warmUpCache();

            $this->info('Cache warm-up completed successfully!');

            // Show cache stats
            $stats = $cacheService->getCacheStats();
            $this->table(
                ['Cache Key', 'Status'],
                [
                    ['Products', $stats['products_cached'] ? '✓ Cached' : '✗ Not Cached'],
                    ['Categories', $stats['categories_cached'] ? '✓ Cached' : '✗ Not Cached'],
                    ['Settings', $stats['settings_cached'] ? '✓ Cached' : '✗ Not Cached'],
                    ['Dashboard Stats', $stats['dashboard_cached'] ? '✓ Cached' : '✗ Not Cached'],
                    ['Low Stock Products', $stats['low_stock_cached'] ? '✓ Cached' : '✗ Not Cached'],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error('Cache warm-up failed: ' . $e->getMessage());
            return 1;
        }
    }
}
