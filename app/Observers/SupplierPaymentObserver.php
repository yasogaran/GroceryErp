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
        // Post to accounting after the transaction commits
        // Use database transaction committed callback
        \DB::afterCommit(function () use ($payment) {
            // Use a slight delay to ensure all related records are persisted
            usleep(100000); // 100ms delay

            try {
                // Reload the payment with fresh data from database
                $freshPayment = SupplierPayment::find($payment->id);

                if (!$freshPayment) {
                    Log::warning("Supplier Payment #{$payment->id} not found after creation", [
                        'original_payment_id' => $payment->id,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    return;
                }

                Log::info("Processing supplier payment for accounting", [
                    'payment_id' => $freshPayment->id,
                    'reference_number' => $freshPayment->reference_number,
                    'amount' => $freshPayment->amount,
                    'payment_mode' => $freshPayment->payment_mode
                ]);

                // Check if already posted to avoid duplicates
                if (!$this->transactionService->isPosted(SupplierPayment::class, $freshPayment->id)) {
                    $this->transactionService->postSupplierPayment($freshPayment);
                    Log::info("Supplier Payment #{$freshPayment->reference_number} posted to accounting successfully", [
                        'payment_id' => $freshPayment->id,
                        'amount' => $freshPayment->amount,
                        'payment_mode' => $freshPayment->payment_mode
                    ]);
                } else {
                    Log::info("Supplier Payment #{$freshPayment->reference_number} already posted, skipping");
                }
            } catch (\Exception $e) {
                // Log error but don't fail the payment creation
                Log::error("Failed to post supplier payment #{$payment->reference_number} to accounting: " . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        });
    }
}
