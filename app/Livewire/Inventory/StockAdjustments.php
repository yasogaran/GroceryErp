<?php

namespace App\Livewire\Inventory;

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

    public function createAdjustment()
    {
        $this->validate();

        try {
            StockAdjustment::create([
                'product_id' => $this->productId,
                'adjustment_type' => $this->adjustmentType,
                'quantity' => $this->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock adjustment request created successfully'
            ]);

            // Reset form
            $this->reset(['productId', 'adjustmentType', 'quantity', 'reason', 'notes']);
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
        ]);
    }
}
