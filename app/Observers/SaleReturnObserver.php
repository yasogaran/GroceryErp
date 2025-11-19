<?php

namespace App\Observers;

use App\Models\SaleReturn;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class SaleReturnObserver
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the SaleReturn "created" event.
     * Automatically post return to accounting system.
     */
    public function created(SaleReturn $return): void
    {
        try {
            // Check if already posted to avoid duplicates
            if (!$this->transactionService->isPosted(SaleReturn::class, $return->id)) {
                $this->transactionService->postSaleReturn($return);
                Log::info("Sale Return #{$return->return_number} posted to accounting successfully");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the return creation
            Log::error("Failed to post return #{$return->return_number} to accounting: " . $e->getMessage(), [
                'return_id' => $return->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
