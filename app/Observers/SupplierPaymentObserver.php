<?php

namespace App\Observers;

use App\Models\SupplierPayment;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;

class SupplierPaymentObserver
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the SupplierPayment "created" event.
     * Automatically post payment to accounting system.
     */
    public function created(SupplierPayment $payment): void
    {
        try {
            // Check if already posted to avoid duplicates
            if (!$this->transactionService->isPosted(SupplierPayment::class, $payment->id)) {
                $this->transactionService->postSupplierPayment($payment);
                Log::info("Supplier Payment #{$payment->reference_number} posted to accounting successfully");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the payment creation
            Log::error("Failed to post supplier payment #{$payment->reference_number} to accounting: " . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
