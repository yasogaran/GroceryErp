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

        // Post to accounting after the transaction commits (when payments will exist)
        // Use DB::afterCommit to ensure all related records (payments, items) are saved
        \DB::afterCommit(function () use ($sale) {
            // Only post to accounting if sale is completed
            if ($sale->status === 'completed') {
                // Reload the sale to get fresh payment relationship data
                $sale->load('payments');

                // Only post if there are payments (skip full credit invoices with 0 payment)
                if ($sale->payments->count() > 0) {
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
        });
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
