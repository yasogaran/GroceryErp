<?php

namespace App\Livewire\Barcodes;

use App\Models\Product;
use App\Models\Category;
use App\Services\BarcodeService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class LabelPrinting extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $selectedProducts = [];
    public $quantities = [];
    public $labelType = 'product'; // product or box
    public $showPreview = false;
    public $previewLabels = [];

    protected $queryString = ['search', 'categoryFilter'];

    public function mount()
    {
        // Check if user has permission
        if (!auth()->user() || !in_array(auth()->user()->role, ['admin', 'manager', 'store_keeper'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function toggleProduct($productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
            unset($this->quantities[$productId]);
        } else {
            $this->selectedProducts[] = $productId;
            $this->quantities[$productId] = 1;
        }
    }

    public function selectAll()
    {
        $products = $this->getFilteredProducts()->pluck('id')->toArray();
        $this->selectedProducts = $products;

        foreach ($products as $productId) {
            if (!isset($this->quantities[$productId])) {
                $this->quantities[$productId] = 1;
            }
        }
    }

    public function deselectAll()
    {
        $this->selectedProducts = [];
        $this->quantities = [];
    }

    public function updateQuantity($productId, $quantity)
    {
        $this->quantities[$productId] = max(1, (int)$quantity);
    }

    public function generatePreview()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Please select at least one product.');
            return;
        }

        $barcodeService = new BarcodeService();
        $this->previewLabels = [];

        foreach ($this->selectedProducts as $productId) {
            $product = Product::with('packaging')->find($productId);
            $quantity = $this->quantities[$productId] ?? 1;

            if ($product) {
                if ($this->labelType === 'box' && $product->has_packaging) {
                    $label = $barcodeService->generateBoxLabel($product, $quantity);
                    if ($label) {
                        $this->previewLabels[] = $label;
                    }
                } else {
                    $label = $barcodeService->generateProductLabel($product, $quantity);
                    $this->previewLabels[] = $label;
                }
            }
        }

        $this->showPreview = true;
    }

    public function closePreview()
    {
        $this->showPreview = false;
    }

    public function printLabels()
    {
        // This will trigger the browser print dialog via JavaScript
        $this->dispatch('print-labels');
    }

    protected function getFilteredProducts()
    {
        $query = Product::query()
            ->where('is_active', true);

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->categoryFilter) {
            $query->byCategory($this->categoryFilter);
        }

        if ($this->labelType === 'box') {
            $query->where('has_packaging', true);
        }

        return $query->with(['category', 'packaging']);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $products = $this->getFilteredProducts()
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::active()->orderBy('name')->get();

        return view('livewire.barcodes.label-printing', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
