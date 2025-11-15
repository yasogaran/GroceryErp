<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;

class EditCategory extends Component
{
    public $categoryId;
    public $name = '';
    public $parent_id = null;
    public $description = '';
    public $is_active = true;

    /**
     * Mount the component.
     */
    public function mount($categoryId)
    {
        $this->categoryId = $categoryId;
        $category = Category::findOrFail($categoryId);

        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
    }

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $this->categoryId],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                'different:categoryId', // Prevent setting itself as parent
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected $messages = [
        'name.required' => 'Category name is required.',
        'name.unique' => 'A category with this name already exists.',
        'parent_id.exists' => 'The selected parent category does not exist.',
        'parent_id.different' => 'A category cannot be its own parent.',
    ];

    /**
     * Update the category.
     */
    public function update()
    {
        $validated = $this->validate();

        $category = Category::findOrFail($this->categoryId);

        // Additional check: Prevent circular reference (can't set a child as parent)
        if ($validated['parent_id']) {
            $parentCategory = Category::find($validated['parent_id']);
            if ($parentCategory && $parentCategory->parent_id == $this->categoryId) {
                session()->flash('error', 'Cannot set a subcategory as parent. This would create a circular reference.');
                return;
            }
        }

        $category->update([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'Category updated successfully.');

        $this->dispatch('category-updated');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        // Get all parent categories except the current one and its children
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $this->categoryId)
            ->orderBy('name')
            ->get();

        return view('livewire.categories.edit-category', [
            'parentCategories' => $parentCategories,
        ]);
    }
}
