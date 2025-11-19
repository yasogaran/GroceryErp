<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\CacheService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    protected $cacheService;
    protected $transactionService;

    public function __construct(CacheService $cacheService, TransactionService $transactionService)
    {
        $this->cacheService = $cacheService;
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the Sale "created" event.
     * Automatically post sale to accounting system.
     */
    public function created(Sale $sale): void
    {
        $this->cacheService->invalidateDashboardCache();

        // Only post to accounting if sale is completed and has payments
        if ($sale->status === 'completed' && $sale->payments()->count() > 0) {
            try {
                // Check if already posted to avoid duplicates
                if (!$this->transactionService->isPosted(Sale::class, $sale->id)) {
                    $this->transactionService->postSale($sale);
                    Log::info("Sale #{$sale->invoice_number} posted to accounting successfully");
                }
            } catch (\Exception $e) {
                // Log error but don't fail the sale creation
                Log::error("Failed to post sale #{$sale->invoice_number} to accounting: " . $e->getMessage(), [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
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
