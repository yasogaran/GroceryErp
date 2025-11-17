<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class StockReport extends Component
{
    public $lowStockOnly = false;
    public $categoryFilter = null;
    public $searchTerm = '';

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Product::with('category');

        // Low stock filter
        if ($this->lowStockOnly) {
            $threshold = settings('low_stock_threshold', 10);
            $query->where('current_stock_quantity', '<=', $threshold);
        }

        // Category filter
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        // Search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $products = $query->orderBy('current_stock_quantity', 'asc')->get();

        // Summary statistics
        $summary = [
            'total_products' => Product::count(),
            'out_of_stock' => Product::where('current_stock_quantity', 0)->count(),
            'low_stock' => Product::where('current_stock_quantity', '<=', settings('low_stock_threshold', 10))
                ->where('current_stock_quantity', '>', 0)
                ->count(),
            'total_value' => Product::sum(DB::raw('current_stock_quantity * max_selling_price')),
        ];

        $categories = Category::all();

        return view('livewire.reports.stock-report', [
            'products' => $products,
            'summary' => $summary,
            'categories' => $categories,
        ]);
    }
}
