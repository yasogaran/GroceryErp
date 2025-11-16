<?php

namespace App\Livewire\Offers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class OfferForm extends Component
{
    public $offerId = null;
    public $isEdit = false;

    // Offer details
    public $name;
    public $description;
    public $offer_type = 'buy_x_get_y';
    public $start_date;
    public $end_date;
    public $priority = 0;
    public $is_active = true;

    // Buy X Get Y
    public $buy_quantity = 2;
    public $get_quantity = 1;

    // Quantity Discount
    public $min_quantity = 5;
    public $discount_type = 'percentage';
    public $discount_value = 10;

    // Products/Categories
    public $selectedProducts = [];
    public $selectedCategories = [];
    public $productSearch = '';

    public function mount($id = null)
    {
        if ($id) {
            $this->offerId = $id;
            $this->isEdit = true;
            $this->loadOffer();
        } else {
            // Set default dates
            $this->start_date = now()->format('Y-m-d');
            $this->end_date = now()->addDays(30)->format('Y-m-d');
        }
    }

    protected function loadOffer()
    {
        $offer = Offer::with('products', 'categories')->findOrFail($this->offerId);

        $this->name = $offer->name;
        $this->description = $offer->description;
        $this->offer_type = $offer->offer_type;
        $this->start_date = $offer->start_date->format('Y-m-d');
        $this->end_date = $offer->end_date->format('Y-m-d');
        $this->priority = $offer->priority;
        $this->is_active = $offer->is_active;

        if ($offer->offer_type === Offer::TYPE_BUY_X_GET_Y) {
            $this->buy_quantity = $offer->buy_quantity;
            $this->get_quantity = $offer->get_quantity;
        } else {
            $this->min_quantity = $offer->min_quantity;
            $this->discount_type = $offer->discount_type;
            $this->discount_value = $offer->discount_value;
        }

        $this->selectedProducts = $offer->products->pluck('id')->toArray();
        $this->selectedCategories = $offer->categories->pluck('id')->toArray();
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'offer_type' => 'required|in:buy_x_get_y,quantity_discount',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|integer|min:0',
        ];

        if ($this->offer_type === 'buy_x_get_y') {
            $rules['buy_quantity'] = 'required|integer|min:1';
            $rules['get_quantity'] = 'required|integer|min:1';
        } else {
            $rules['min_quantity'] = 'required|numeric|min:1';
            $rules['discount_type'] = 'required|in:percentage,fixed';
            $rules['discount_value'] = 'required|numeric|min:0.01';
        }

        return $rules;
    }

    public function toggleProduct($productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function toggleCategory($categoryId)
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_diff($this->selectedCategories, [$categoryId]);
        } else {
            $this->selectedCategories[] = $categoryId;
        }
    }

    public function save()
    {
        $this->validate();

        if (empty($this->selectedProducts) && empty($this->selectedCategories)) {
            session()->flash('error', 'Please select at least one product or category');
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'offer_type' => $this->offer_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'created_by' => auth()->id(),
        ];

        if ($this->offer_type === 'buy_x_get_y') {
            $data['buy_quantity'] = $this->buy_quantity;
            $data['get_quantity'] = $this->get_quantity;
            $data['min_quantity'] = null;
            $data['discount_type'] = null;
            $data['discount_value'] = null;
        } else {
            $data['min_quantity'] = $this->min_quantity;
            $data['discount_type'] = $this->discount_type;
            $data['discount_value'] = $this->discount_value;
            $data['buy_quantity'] = null;
            $data['get_quantity'] = null;
        }

        try {
            DB::transaction(function() use ($data) {
                if ($this->isEdit) {
                    $offer = Offer::findOrFail($this->offerId);
                    $offer->update($data);
                } else {
                    $offer = Offer::create($data);
                }

                // Sync products and categories
                $pivotData = [];

                foreach ($this->selectedProducts as $productId) {
                    $pivotData[] = [
                        'offer_id' => $offer->id,
                        'product_id' => $productId,
                        'category_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                foreach ($this->selectedCategories as $categoryId) {
                    $pivotData[] = [
                        'offer_id' => $offer->id,
                        'product_id' => null,
                        'category_id' => $categoryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Delete existing associations
                DB::table('offer_products')->where('offer_id', $offer->id)->delete();

                // Insert new associations
                DB::table('offer_products')->insert($pivotData);
            });

            session()->flash('success', $this->isEdit ? 'Offer updated successfully' : 'Offer created successfully');

            return redirect()->route('offers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $products = Product::query()
            ->when($this->productSearch, function($q) {
                $q->where('name', 'like', '%' . $this->productSearch . '%')
                  ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
            })
            ->limit(50)
            ->get();

        $categories = Category::all();

        return view('livewire.offers.offer-form', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
