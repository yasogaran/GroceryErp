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

        // Post to accounting after the transaction commits
        // Use database transaction committed callback
        \DB::afterCommit(function () use ($sale) {
            // Use a slight delay to ensure all related records are persisted
            usleep(100000); // 100ms delay

            try {
                // Reload the sale with fresh data from database
                $freshSale = Sale::with('payments')->find($sale->id);

                if (!$freshSale) {
                    Log::warning("Sale #{$sale->id} not found after creation", [
                        'original_sale_id' => $sale->id,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    return;
                }

                Log::info("Processing sale for accounting", [
                    'sale_id' => $freshSale->id,
                    'invoice_number' => $freshSale->invoice_number,
                    'status' => $freshSale->status,
                    'payment_count' => $freshSale->payments->count()
                ]);

                // Only post to accounting if sale is completed
                if ($freshSale->status === 'completed') {
                    // Only post if there are payments (skip full credit invoices with 0 payment)
                    if ($freshSale->payments->count() > 0) {
                        // Check if already posted to avoid duplicates
                        if (!$this->transactionService->isPosted(Sale::class, $freshSale->id)) {
                            $this->transactionService->postSale($freshSale);
                            Log::info("Sale #{$freshSale->invoice_number} posted to accounting successfully", [
                                'sale_id' => $freshSale->id,
                                'total_amount' => $freshSale->total_amount
                            ]);
                        } else {
                            Log::info("Sale #{$freshSale->invoice_number} already posted, skipping");
                        }
                    } else {
                        Log::info("Sale #{$freshSale->invoice_number} has no payments, skipping accounting post");
                    }
                } else {
                    Log::info("Sale #{$freshSale->invoice_number} not completed (status: {$freshSale->status}), skipping accounting post");
                }
            } catch (\Exception $e) {
                // Log error but don't fail the sale creation
                Log::error("Failed to post sale #{$sale->invoice_number} to accounting: " . $e->getMessage(), [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
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
