<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CreateCategory extends Component
{
    public $name = '';
    public $parent_id = null;
    public $description = '';
    public $is_active = true;

    /**
     * Validation rules.
     */
    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'parent_id' => ['nullable', 'exists:categories,id'],
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
    ];

    /**
     * Save the new category.
     */
    public function save()
    {
        $validated = $this->validate();

        Category::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
        ]);

        session()->flash('success', 'Category created successfully.');

        $this->dispatch('category-created');
        $this->reset();
    }

    /**
     * Render the component.
     */
    #[Layout('layouts.app')]
    public function render()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('livewire.categories.create-category', [
            'parentCategories' => $parentCategories,
        ]);
    }
}
