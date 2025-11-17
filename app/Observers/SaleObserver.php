<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\CacheService;

class SaleObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        $this->cacheService->invalidateDashboardCache();
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        $this->cacheService->invalidateDashboardCache();
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        $this->cacheService->invalidateDashboardCache();
    }
}
