<?php

namespace App\Livewire\Inventory;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockAdjustment;
use App\Models\Product;

class StockAdjustments extends Component
{
    use WithPagination;

    public $activeTab = 'pending'; // pending, approved, rejected, create
    public $searchTerm = '';

    // Create adjustment
    public $productId = null;
    public $adjustmentType = 'increase';
    public $quantity = 0;
    public $reason = 'counting_error';
    public $notes = '';
    public $availableBatches = [];
    public $selectedBatchId = null;
    public $selectedProduct = null;

    protected $rules = [
        'productId' => 'required|exists:products,id',
        'adjustmentType' => 'required|in:increase,decrease',
        'quantity' => 'required|numeric|min:0.01',
        'reason' => 'required|in:counting_error,theft,sampling,expiry,other',
        'notes' => 'nullable|string|max:500',
    ];

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $this->selectedProduct = Product::find($value);

            // Get available batches for all adjustment types
            $inventoryService = app(\App\Services\InventoryService::class);
            $this->availableBatches = $inventoryService->getAvailableBatches($this->selectedProduct);

            // Calculate remaining quantity for each batch
            foreach ($this->availableBatches as &$batch) {
                $batch['remaining_quantity'] = $this->calculateBatchRemainingQuantity($value, $batch['stock_movement_id']);
            }

            // For decrease adjustments, filter out batches with no remaining quantity
            if ($this->adjustmentType === 'decrease') {
                $this->availableBatches = array_filter($this->availableBatches, function($batch) {
                    return $batch['remaining_quantity'] > 0;
                });
            }

            // Reset selection when batches change
            $this->selectedBatchId = null;
        } else {
            $this->selectedProduct = null;
            $this->availableBatches = [];
            $this->selectedBatchId = null;
        }
    }

    public function updatedAdjustmentType($value)
    {
        // Reload batches when adjustment type changes
        if ($this->productId) {
            $this->updatedProductId($this->productId);
        }
    }

    public function createAdjustment()
    {
        $this->validate();

        // Require batch selection when batches are available
        if (count($this->availableBatches) > 0 && !$this->selectedBatchId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select a batch'
            ]);
            return;
        }

        // Validate quantity against batch for decrease adjustments
        if ($this->selectedBatchId && $this->adjustmentType === 'decrease') {
            $selectedBatch = collect($this->availableBatches)->firstWhere('stock_movement_id', $this->selectedBatchId);
            if ($selectedBatch && $this->quantity > $selectedBatch['remaining_quantity']) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => "Insufficient stock in selected batch. Available: {$selectedBatch['remaining_quantity']}"
                ]);
                return;
            }
        }

        try {
            $adjustmentData = [
                'product_id' => $this->productId,
                'adjustment_type' => $this->adjustmentType,
                'quantity' => $this->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ];

            // Add batch ID if selected
            if ($this->selectedBatchId) {
                $adjustmentData['batch_id'] = $this->selectedBatchId;
            }

            StockAdjustment::create($adjustmentData);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock adjustment request created successfully'
            ]);

            // Reset form
            $this->reset(['productId', 'adjustmentType', 'quantity', 'reason', 'notes', 'availableBatches', 'selectedBatchId', 'selectedProduct']);
            $this->activeTab = 'pending';

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error creating adjustment: ' . $e->getMessage()
            ]);
        }
    }

    public function approveAdjustment($adjustmentId)
    {
        try {
            $adjustment = StockAdjustment::findOrFail($adjustmentId);

            if (!$adjustment->canBeApproved()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'This adjustment cannot be approved'
                ]);
                return;
            }

            // Check if user is trying to approve their own adjustment
            if ($adjustment->created_by === auth()->id()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'You cannot approve your own adjustment'
                ]);
                return;
            }

            $adjustment->approve(auth()->user());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock adjustment approved and applied successfully'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error approving adjustment: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectAdjustment($adjustmentId)
    {
        try {
            $adjustment = StockAdjustment::findOrFail($adjustmentId);

            if (!$adjustment->canBeRejected()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'This adjustment cannot be rejected'
                ]);
                return;
            }

            $adjustment->reject(auth()->user());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock adjustment rejected'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error rejecting adjustment: ' . $e->getMessage()
            ]);
        }
    }

    private function calculateBatchRemainingQuantity($productId, $batchId)
    {
        // Get initial batch quantity
        $batch = \App\Models\StockMovement::find($batchId);
        if (!$batch) {
            return 0;
        }

        $initialQty = $batch->quantity;

        // Get total outgoing movements for this batch using the new source_stock_movement_id field
        $outgoingQty = \App\Models\StockMovement::where('product_id', $productId)
            ->whereIn('movement_type', ['out', 'damage', 'write_off'])
            ->where('source_stock_movement_id', $batchId)
            ->sum('quantity');

        return $initialQty - abs($outgoingQty);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = StockAdjustment::with(['product', 'creator', 'approver']);

        if ($this->activeTab !== 'create') {
            $query->where('status', $this->activeTab);
        }

        if ($this->searchTerm) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $adjustments = $this->activeTab === 'create'
            ? collect()
            : $query->latest()->paginate(20);

        $products = Product::orderBy('name')->get();

        // Summary statistics
        $summary = [
            'pending_count' => StockAdjustment::pending()->count(),
            'approved_count' => StockAdjustment::approved()->count(),
            'rejected_count' => StockAdjustment::rejected()->count(),
        ];

        return view('livewire.inventory.stock-adjustments', [
            'adjustments' => $adjustments,
            'products' => $products,
            'summary' => $summary,
            'availableBatches' => $this->availableBatches,
        ]);
    }
}
