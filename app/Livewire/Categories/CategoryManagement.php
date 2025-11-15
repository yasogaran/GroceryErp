<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedCategoryId = null;

    protected $queryString = ['search', 'statusFilter'];

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
    public function openEditModal($categoryId)
    {
        $this->selectedCategoryId = $categoryId;
        $this->showEditModal = true;
    }

    /**
     * Close all modals.
     */
    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedCategoryId = null;
    }

    /**
     * Listen for category-created event.
     */
    #[On('category-created')]
    public function categoryCreated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Listen for category-updated event.
     */
    #[On('category-updated')]
    public function categoryUpdated()
    {
        $this->closeModals();
        $this->resetPage();
    }

    /**
     * Toggle category active status.
     */
    public function toggleCategoryStatus($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->is_active = !$category->is_active;
        $category->save();

        session()->flash('success', $category->is_active ? 'Category activated successfully.' : 'Category deactivated successfully.');
    }

    /**
     * Delete a category.
     */
    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        // Check if category can be deleted
        if (!$category->canBeDeleted()) {
            session()->flash('error', 'Cannot delete category. It has subcategories or products assigned to it.');
            return;
        }

        $category->delete();
        session()->flash('success', 'Category deleted successfully.');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->with(['parent', 'children'])
            ->orderBy('parent_id', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('livewire.categories.category-management', [
            'categories' => $categories,
        ]);
    }
}
