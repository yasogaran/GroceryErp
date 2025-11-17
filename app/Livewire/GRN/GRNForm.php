<?php

namespace App\Livewire\GRN;

use App\Models\GRN;
use App\Models\GRNItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GRNForm extends Component
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
            session()->flash('item_error', 'Please fill all required fields correctly');
            return;
        }

        if ($this->min_selling_price <= 0 || $this->max_selling_price <= 0) {
            session()->flash('item_error', 'Please enter valid selling prices');
            return;
        }

        if ($this->min_selling_price < $this->unit_price) {
            session()->flash('item_error', 'Minimum selling price must be greater than or equal to unit cost');
            return;
        }

        if ($this->max_selling_price < $this->min_selling_price) {
            session()->flash('item_error', 'Maximum selling price must be greater than or equal to minimum selling price');
            return;
        }

        // Check if product already exists in items
        foreach ($this->items as $item) {
            if ($item['product_id'] == $this->product_id) {
                session()->flash('item_error', 'Product already added. Please edit the existing item.');
                return;
            }
        }

        $product = Product::find($this->product_id);
        $total_amount = $this->received_pieces * $this->unit_price;

        $this->items[] = [
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

        // Reset form
        $this->resetItemForm();
        session()->flash('item_success', 'Item added successfully');
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
        $this->validate();

        if (count($this->items) === 0) {
            session()->flash('error', 'Please add at least one item to the GRN');
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
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('livewire.grn.grn-form', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }
}
