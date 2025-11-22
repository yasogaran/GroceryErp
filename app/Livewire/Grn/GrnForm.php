<?php

namespace App\Livewire\Grn;

use App\Models\GRN;
use App\Models\GRNItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GrnForm extends Component
{
    public $grnId;
    public $grn_number;
    public $supplier_id;
    public $grn_date;
    public $notes = '';
    public $status = 'draft';

    public $items = [];
    public $product_id = '';
    public $received_boxes = 0;
    public $received_pieces = 0;
    public $unit_price = 0;
    public $min_selling_price = 0;
    public $max_selling_price = 0;
    public $batch_number = '';
    public $manufacturing_date = '';
    public $expiry_date = '';
    public $item_notes = '';

    public $isEditMode = false;
    public $selectedProduct = null;
    public $editingItemIndex = null; // Track which item is being edited

    // Product filtering
    public $categoryFilter = '';
    public $productSearch = '';

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'grn_date' => 'required|date',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.received_pieces' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.min_selling_price' => 'required|numeric|min:0|gte:items.*.unit_price',
        'items.*.max_selling_price' => 'required|numeric|min:0|gte:items.*.min_selling_price',
    ];

    public function mount($id = null)
    {
        $this->grn_date = now()->format('Y-m-d');

        if ($id) {
            $this->isEditMode = true;
            $this->grnId = $id;
            $this->loadGRN();
        } else {
            $this->grn_number = GRN::generateGRNNumber();
        }
    }

    public function loadGRN()
    {
        $grn = GRN::with('items.product')->findOrFail($this->grnId);

        if (!$grn->canEdit()) {
            session()->flash('error', 'Cannot edit approved GRN');
            return redirect()->route('grn.index');
        }

        $this->grn_number = $grn->grn_number;
        $this->supplier_id = $grn->supplier_id;
        $this->grn_date = $grn->grn_date->format('Y-m-d');
        $this->notes = $grn->notes;
        $this->status = $grn->status;

        // Load items
        $this->items = [];
        foreach ($grn->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'received_boxes' => $item->received_boxes,
                'received_pieces' => $item->received_pieces,
                'unit_price' => $item->unit_price,
                'min_selling_price' => $item->min_selling_price ?? $item->product->min_selling_price ?? 0,
                'max_selling_price' => $item->max_selling_price ?? $item->product->max_selling_price ?? 0,
                'total_amount' => $item->total_amount,
                'batch_number' => $item->batch_number,
                'manufacturing_date' => $item->manufacturing_date?->format('Y-m-d'),
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                'notes' => $item->notes,
            ];
        }
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $this->selectedProduct = Product::with('packaging')->find($value);
            // Auto-fill current selling prices as default
            $this->min_selling_price = $this->selectedProduct->min_selling_price ?? 0;
            $this->max_selling_price = $this->selectedProduct->max_selling_price ?? 0;
            $this->calculatePieces();
        } else {
            $this->selectedProduct = null;
            $this->received_pieces = 0;
            $this->min_selling_price = 0;
            $this->max_selling_price = 0;
        }
    }

    public function updatedReceivedBoxes()
    {
        $this->calculatePieces();
    }

    public function calculatePieces()
    {
        if ($this->selectedProduct && $this->selectedProduct->has_packaging) {
            $packaging = $this->selectedProduct->packaging()->first();
            if ($packaging && $this->received_boxes > 0) {
                $this->received_pieces = $this->received_boxes * $packaging->pieces_per_package;
            }
        }
    }

    public function addItem()
    {
        if (!$this->product_id || $this->received_pieces <= 0 || $this->unit_price < 0) {
            $this->dispatch('showToast', message: 'Please fill all required fields correctly', type: 'error');
            return;
        }

        if ($this->min_selling_price <= 0 || $this->max_selling_price <= 0) {
            $this->dispatch('showToast', message: 'Please enter valid selling prices', type: 'error');
            return;
        }

        if ($this->min_selling_price < $this->unit_price) {
            $this->dispatch('showToast', message: 'Minimum selling price must be greater than or equal to unit cost', type: 'error');
            return;
        }

        if ($this->max_selling_price < $this->min_selling_price) {
            $this->dispatch('showToast', message: 'Maximum selling price must be greater than or equal to minimum selling price', type: 'error');
            return;
        }

        // Check if product already exists in items (but not the one being edited)
        foreach ($this->items as $index => $item) {
            if ($item['product_id'] == $this->product_id && $index !== $this->editingItemIndex) {
                $this->dispatch('showToast', message: 'Product already added. Please edit the existing item.', type: 'error');
                return;
            }
        }

        $product = Product::find($this->product_id);
        $total_amount = $this->received_pieces * $this->unit_price;

        $itemData = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'received_boxes' => $this->received_boxes,
            'received_pieces' => $this->received_pieces,
            'unit_price' => $this->unit_price,
            'min_selling_price' => $this->min_selling_price,
            'max_selling_price' => $this->max_selling_price,
            'total_amount' => $total_amount,
            'batch_number' => $this->batch_number,
            'manufacturing_date' => $this->manufacturing_date,
            'expiry_date' => $this->expiry_date,
            'notes' => $this->item_notes,
        ];

        if ($this->editingItemIndex !== null) {
            // Update existing item
            $this->items[$this->editingItemIndex] = $itemData;
            $this->dispatch('showToast', message: 'Item updated successfully', type: 'success');
            $this->editingItemIndex = null;
        } else {
            // Add new item
            $this->items[] = $itemData;
            $this->dispatch('showToast', message: 'Item added successfully', type: 'success');
        }

        // Reset form
        $this->resetItemForm();
    }

    public function editItem($index)
    {
        $item = $this->items[$index];

        $this->editingItemIndex = $index;
        $this->product_id = $item['product_id'];
        $this->selectedProduct = Product::with('packaging')->find($item['product_id']);
        $this->received_boxes = $item['received_boxes'];
        $this->received_pieces = $item['received_pieces'];
        $this->unit_price = $item['unit_price'];
        $this->min_selling_price = $item['min_selling_price'];
        $this->max_selling_price = $item['max_selling_price'];
        $this->batch_number = $item['batch_number'];
        $this->manufacturing_date = $item['manufacturing_date'];
        $this->expiry_date = $item['expiry_date'];
        $this->item_notes = $item['notes'] ?? '';
    }

    public function cancelEdit()
    {
        $this->editingItemIndex = null;
        $this->resetItemForm();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
    }

    public function resetItemForm()
    {
        $this->product_id = '';
        $this->received_boxes = 0;
        $this->received_pieces = 0;
        $this->unit_price = 0;
        $this->min_selling_price = 0;
        $this->max_selling_price = 0;
        $this->batch_number = '';
        $this->manufacturing_date = '';
        $this->expiry_date = '';
        $this->item_notes = '';
        $this->selectedProduct = null;
    }

    public function save()
    {
        try {
            $this->validate();

            if (count($this->items) === 0) {
                $this->dispatch('showToast', message: 'Please add at least one item to the GRN', type: 'error');
                return;
            }

            DB::transaction(function () {
                $total_amount = array_sum(array_column($this->items, 'total_amount'));

                $grnData = [
                    'grn_number' => $this->grn_number,
                    'supplier_id' => $this->supplier_id,
                    'grn_date' => $this->grn_date,
                    'total_amount' => $total_amount,
                    'status' => 'draft',
                    'notes' => $this->notes,
                    'created_by' => auth()->id(),
                ];

                if ($this->isEditMode) {
                    $grn = GRN::findOrFail($this->grnId);
                    $grn->update($grnData);

                    // Delete existing items
                    $grn->items()->delete();
                } else {
                    $grn = GRN::create($grnData);
                }

                // Create items
                foreach ($this->items as $item) {
                    GRNItem::create([
                        'grn_id' => $grn->id,
                        'product_id' => $item['product_id'],
                        'received_boxes' => $item['received_boxes'],
                        'received_pieces' => $item['received_pieces'],
                        'unit_price' => $item['unit_price'],
                        'min_selling_price' => $item['min_selling_price'] ?? null,
                        'max_selling_price' => $item['max_selling_price'] ?? null,
                        'total_amount' => $item['total_amount'],
                        'batch_number' => $item['batch_number'],
                        'manufacturing_date' => $item['manufacturing_date'] ?: null,
                        'expiry_date' => $item['expiry_date'] ?: null,
                        'notes' => $item['notes'],
                    ]);
                }

                session()->flash('success', $this->isEditMode ? 'GRN updated successfully' : 'GRN created successfully');
            });

            return redirect()->route('grn.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('showToast', message: 'Please check the form for errors', type: 'error');
            throw $e;
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        // Filter products based on category and search
        $productsQuery = Product::with('category')->active();

        if ($this->categoryFilter) {
            $productsQuery->where('category_id', $this->categoryFilter);
        }

        if ($this->productSearch) {
            $productsQuery->where(function($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%')
                      ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
            });
        }

        $products = $productsQuery->orderBy('name')->get();

        return view('livewire.grn.grn-form', [
            'suppliers' => $suppliers,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
