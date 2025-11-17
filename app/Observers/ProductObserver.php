<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;

class ProductObserver
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->cacheService->invalidateProductCache($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->cacheService->invalidateProductCache($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->cacheService->invalidateProductCache($product);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->cacheService->invalidateProductCache($product);
    }
}
