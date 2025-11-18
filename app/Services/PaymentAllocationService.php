<?php

namespace App\Services;

use App\Models\GRN;
use App\Models\SupplierPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentAllocationService
{
    /**
     * Allocate a payment to GRNs using water-fill algorithm (oldest first).
     *
     * @param SupplierPayment $payment
     * @param array|null $manualAllocations Optional manual allocations: ['grn_id' => amount]
     * @return array Array of GRNPayment records created
     * @throws \Exception
     */
    public function allocatePayment(SupplierPayment $payment, ?array $manualAllocations = null): array
    {
        return DB::transaction(function () use ($payment, $manualAllocations) {
            $allocations = [];

            if ($manualAllocations !== null) {
                // Manual allocation mode
                $allocations = $this->allocateManually($payment, $manualAllocations);
            } else {
                // Automatic water-fill allocation
                $allocations = $this->allocateAutomatically($payment);
            }

            return $allocations;
        });
    }

    /**
     * Automatically allocate payment to GRNs from oldest to newest (water-fill).
     *
     * @param SupplierPayment $payment
     * @return array
     * @throws \Exception
     */
    protected function allocateAutomatically(SupplierPayment $payment): array
    {
        $remainingAmount = $payment->amount;
        $allocations = [];

        // Get all approved GRNs with outstanding balance for this supplier
        // Ordered by GRN date (oldest first)
        $query = GRN::where('supplier_id', $payment->supplier_id)
            ->where('status', GRN::STATUS_APPROVED)
            ->orderBy('grn_date', 'asc')
            ->orderBy('id', 'asc');

        // Check if payment_status column exists (migration has been run)
        if ($this->hasPaymentStatusColumn()) {
            $query->withOutstanding();
        }

        $grns = $query->get();

        foreach ($grns as $grn) {
            if ($remainingAmount <= 0) {
                break;
            }

            $grnOutstanding = $grn->getOutstandingAmount();

            if ($grnOutstanding <= 0) {
                continue;
            }

            // Allocate as much as possible to this GRN
            $allocationAmount = min($remainingAmount, $grnOutstanding);

            $grnPayment = $grn->recordPayment($payment->id, $allocationAmount);
            $allocations[] = $grnPayment;

            $remainingAmount -= $allocationAmount;
        }

        if ($remainingAmount > 0.01) { // Allow for floating point precision
            throw new \Exception("Payment amount could not be fully allocated. Remaining: {$remainingAmount}");
        }

        return $allocations;
    }

    /**
     * Manually allocate payment to specific GRNs with specific amounts.
     *
     * @param SupplierPayment $payment
     * @param array $allocations ['grn_id' => amount]
     * @return array
     * @throws \Exception
     */
    protected function allocateManually(SupplierPayment $payment, array $allocations): array
    {
        $totalAllocated = array_sum($allocations);

        if (abs($totalAllocated - $payment->amount) > 0.01) { // Allow for floating point precision
            throw new \Exception("Total allocated amount ({$totalAllocated}) must equal payment amount ({$payment->amount})");
        }

        $grnPayments = [];

        foreach ($allocations as $grnId => $amount) {
            if ($amount <= 0) {
                continue;
            }

            $grn = GRN::findOrFail($grnId);

            // Validate supplier matches
            if ($grn->supplier_id !== $payment->supplier_id) {
                throw new \Exception("GRN #{$grnId} does not belong to the payment's supplier");
            }

            // Validate GRN is approved
            if ($grn->status !== GRN::STATUS_APPROVED) {
                throw new \Exception("GRN #{$grnId} is not approved");
            }

            $grnPayment = $grn->recordPayment($payment->id, $amount);
            $grnPayments[] = $grnPayment;
        }

        return $grnPayments;
    }

    /**
     * Get suggested allocation for a payment amount to a supplier's GRNs.
     *
     * @param int $supplierId
     * @param float $amount
     * @return Collection Collection of suggested allocations with GRN details
     */
    public function getSuggestedAllocation(int $supplierId, float $amount): Collection
    {
        $remainingAmount = $amount;
        $suggestions = collect();

        // Get all approved GRNs with outstanding balance
        $query = GRN::where('supplier_id', $supplierId)
            ->where('status', GRN::STATUS_APPROVED)
            ->orderBy('grn_date', 'asc')
            ->orderBy('id', 'asc');

        // Check if payment_status column exists (migration has been run)
        if ($this->hasPaymentStatusColumn()) {
            $query->withOutstanding();
        }

        $grns = $query->get();

        foreach ($grns as $grn) {
            if ($remainingAmount <= 0) {
                break;
            }

            $grnOutstanding = $grn->getOutstandingAmount();

            if ($grnOutstanding <= 0) {
                continue;
            }

            // Calculate allocation for this GRN
            $allocationAmount = min($remainingAmount, $grnOutstanding);

            $suggestions->push([
                'grn' => $grn,
                'grn_id' => $grn->id,
                'grn_number' => $grn->grn_number,
                'grn_date' => $grn->grn_date,
                'total_amount' => $grn->total_amount,
                'paid_amount' => $this->getPaidAmount($grn),
                'outstanding_amount' => $grnOutstanding,
                'allocated_amount' => $allocationAmount,
                'payment_status' => $this->getPaymentStatus($grn),
            ]);

            $remainingAmount -= $allocationAmount;
        }

        return $suggestions;
    }

    /**
     * Get all GRNs with outstanding balance for a supplier.
     *
     * @param int $supplierId
     * @return Collection
     */
    public function getOutstandingGRNs(int $supplierId): Collection
    {
        $query = GRN::where('supplier_id', $supplierId)
            ->where('status', GRN::STATUS_APPROVED)
            ->orderBy('grn_date', 'asc')
            ->orderBy('id', 'asc');

        // Check if payment_status column exists (migration has been run)
        if ($this->hasPaymentStatusColumn()) {
            $query->withOutstanding();
        }

        return $query->get()
            ->map(function ($grn) {
                return [
                    'grn' => $grn,
                    'grn_id' => $grn->id,
                    'grn_number' => $grn->grn_number,
                    'grn_date' => $grn->grn_date,
                    'total_amount' => $grn->total_amount,
                    'paid_amount' => $this->getPaidAmount($grn),
                    'outstanding_amount' => $grn->getOutstandingAmount(),
                    'payment_status' => $this->getPaymentStatus($grn),
                ];
            });
    }

    /**
     * Check if payment_status column exists in grns table.
     *
     * @return bool
     */
    protected function hasPaymentStatusColumn(): bool
    {
        try {
            return \Schema::hasColumn('grns', 'payment_status');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get paid amount for GRN (handles column not existing).
     *
     * @param GRN $grn
     * @return float
     */
    protected function getPaidAmount(GRN $grn): float
    {
        if ($this->hasPaymentStatusColumn() && isset($grn->paid_amount)) {
            return $grn->paid_amount;
        }
        return 0;
    }

    /**
     * Get payment status for GRN (handles column not existing).
     *
     * @param GRN $grn
     * @return string
     */
    protected function getPaymentStatus(GRN $grn): string
    {
        if ($this->hasPaymentStatusColumn() && isset($grn->payment_status)) {
            return $grn->payment_status;
        }
        return GRN::PAYMENT_STATUS_UNPAID;
    }
}
