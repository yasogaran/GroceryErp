<?php

namespace App\Livewire\POS;

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

    protected $listeners = ['resetSearch' => 'resetSearch'];

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

        // Check stock
        $quantity = $isBoxSale && $product->packaging
            ? $product->packaging->pieces_per_package
            : 1;

        if ($product->current_stock_quantity < $quantity) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Insufficient stock for ' . $product->name
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

        // Get total outgoing movements for this batch
        $outgoingQty = \App\Models\StockMovement::where('product_id', $productId)
            ->whereIn('movement_type', ['out', 'damage', 'write_off'])
            ->where('reference_type', 'App\\Models\\StockMovement')
            ->where('reference_id', $batchId)
            ->sum('quantity');

        return $initialQty - abs($outgoingQty);
    }
}
