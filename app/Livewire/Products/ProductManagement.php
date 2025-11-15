<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedProductId = null;

    protected $queryString = ['search', 'categoryFilter', 'statusFilter'];

    /**
     * Reset pagination when search is updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Open the create modal.
     */
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    /**
     * Open the edit modal.
     */
    public function openEditModal($productId)
    {
        $this->selectedProductId = $productId;
        $this->showEditModal = true;
    }

    /**
     * Close all modals.
     */
    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedProductId = null;
    }

    /**
     * Listen for product-created event.
     */
    #[On('product-created')]
    public function productCreated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Listen for product-updated event.
     */
    #[On('product-updated')]
    public function productUpdated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Toggle product active status.
     */
    public function toggleProductStatus($productId)
    {
        $product = Product::findOrFail($productId);
        $product->is_active = !$product->is_active;
        $product->updated_by = auth()->id();
        $product->save();

        session()->flash('success', $product->is_active ? 'Product activated successfully.' : 'Product deactivated successfully.');
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);

        // Check if product can be deleted
        if (!$product->canBeDeleted()) {
            session()->flash('error', 'Cannot delete product. It has existing stock.');
            return;
        }

        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->byCategory($this->categoryFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->with(['category', 'packaging'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::active()->orderBy('name')->get();

        return view('livewire.products.product-management', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
