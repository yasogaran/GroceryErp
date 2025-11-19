<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Services\InventoryService;

class ProductSearch extends Component
{
    public $searchTerm = '';
    public $selectedCategory = null;
    public $viewMode = 'grid'; // grid or list
    public $showBatchSelection = true; // Toggle for batch selection mode

    protected $listeners = [
        'resetSearch' => 'resetSearch',
        'paymentCompleted' => 'refreshProducts',
        'toggleBatchMode' => 'toggleBatchSelection',
        'setViewMode' => 'setViewMode',
    ];

    public function mount()
    {
        // Initialize and dispatch initial state to parent via browser events
        $this->js("window.dispatchEvent(new CustomEvent('batchModeChanged', { detail: { enabled: " . ($this->showBatchSelection ? 'true' : 'false') . " } }))");
        $this->js("window.dispatchEvent(new CustomEvent('viewModeChanged', { detail: { mode: '{$this->viewMode}' } }))");
    }

    public function updated($property)
    {
        // Re-search when searchTerm or category changes
        if (in_array($property, ['searchTerm', 'selectedCategory'])) {
            $this->dispatch('productsUpdated');
        }
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function clearCategory()
    {
        $this->selectedCategory = null;
    }

    public function toggleBatchSelection()
    {
        $this->showBatchSelection = !$this->showBatchSelection;

        // Notify parent component about state change via browser event
        $this->js("window.dispatchEvent(new CustomEvent('batchModeChanged', { detail: { enabled: " . ($this->showBatchSelection ? 'true' : 'false') . " } }))");
    }

    public function setViewMode($mode)
    {
        if (in_array($mode, ['grid', 'list'])) {
            $this->viewMode = $mode;

            // Notify parent component about state change via browser event
            $this->js("window.dispatchEvent(new CustomEvent('viewModeChanged', { detail: { mode: '{$this->viewMode}' } }))");
        }
    }

    public function addToCart($productId, $isBoxSale = false, $batchId = null)
    {
        $product = Product::with('packaging')->find($productId);

        if (!$product) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Product not found'
            ]);
            return;
        }

        // Check stock with detailed error message
        $quantity = $isBoxSale && $product->packaging
            ? $product->packaging->pieces_per_package
            : 1;

        if ($product->current_stock_quantity < $quantity) {
            if ($isBoxSale) {
                $message = "Cannot sell {$product->name} as box. " .
                           "Need {$quantity} pieces for 1 box, " .
                           "but only {$product->current_stock_quantity} pieces available in stock. " .
                           "Not enough pieces for box sale.";
            } else {
                $message = "Insufficient stock for {$product->name}. " .
                           "Trying to add {$quantity} piece, " .
                           "but only {$product->current_stock_quantity} pieces available.";
            }
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $message
            ]);
            return;
        }

        // Emit to parent (POSInterface) with batch_id
        $this->dispatch('productAdded', $productId, $isBoxSale, $batchId);

        // Clear search after adding
        $this->searchTerm = '';

        // Auto-focus back to search
        $this->dispatch('focusSearch');
    }

    public function resetSearch()
    {
        $this->reset(['searchTerm', 'selectedCategory']);
    }

    /**
     * Refresh products after sale completion to show updated stock
     */
    public function refreshProducts()
    {
        // Force Livewire to re-render by updating a property
        // This ensures products are re-queried from database with fresh stock quantities
        $this->viewMode = $this->viewMode; // Touch a property to force re-render
        $this->dispatch('productsUpdated');
    }

    public function render()
    {
        $query = Product::with(['category', 'packaging'])
            ->where('current_stock_quantity', '>', 0);

        // Search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('barcode', $this->searchTerm);
            });
        }

        // Category filter
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        $products = $query->limit(20)->get();

        // If batch selection is enabled, get batches for each product
        $productBatches = [];
        if ($this->showBatchSelection) {
            $inventoryService = app(InventoryService::class);

            foreach ($products as $product) {
                $batches = $inventoryService->getAvailableBatches($product);

                // Calculate remaining quantity for each batch
                foreach ($batches as &$batch) {
                    $batch['remaining_quantity'] = $this->calculateBatchRemainingQuantity($product->id, $batch['stock_movement_id']);
                }

                // Only include batches with remaining quantity
                $batches = array_filter($batches, function($batch) {
                    return $batch['remaining_quantity'] > 0;
                });

                $productBatches[$product->id] = $batches;
            }
        }

        // Auto-select if exact barcode match
        if (strlen($this->searchTerm) >= 6 && $products->count() === 1) {
            $exactMatch = $products->first();

            // Check if barcode matches (for simplicity, check main barcode)
            if ($exactMatch->barcode === $this->searchTerm) {
                $this->addToCart($exactMatch->id, false);
                $this->searchTerm = '';
            }
        }

        $categories = Category::whereNull('parent_id')
            ->withCount('products')
            ->get();

        return view('livewire.pos.product-search', [
            'products' => $products,
            'categories' => $categories,
            'productBatches' => $productBatches,
        ]);
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
}
