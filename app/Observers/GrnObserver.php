<?php

namespace App\Observers;

use App\Models\GRN;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class GrnObserver
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the GRN "updated" event.
     * Post to accounting when GRN is approved.
     */
    public function updated(GRN $grn): void
    {
        // Only post to accounting when status changes to approved
        if ($grn->status === 'approved' && $grn->isDirty('status')) {
            try {
                // Check if already posted to avoid duplicates
                if (!$this->transactionService->isPosted(GRN::class, $grn->id)) {
                    $this->transactionService->postPurchase($grn);
                    Log::info("GRN #{$grn->grn_number} posted to accounting successfully");
                }
            } catch (\Exception $e) {
                // Log error but don't fail the GRN approval
                Log::error("Failed to post GRN #{$grn->grn_number} to accounting: " . $e->getMessage(), [
                    'grn_id' => $grn->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
}
