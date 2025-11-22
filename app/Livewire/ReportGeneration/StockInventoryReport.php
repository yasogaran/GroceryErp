<?php

namespace App\Livewire\ReportGeneration;

use App\Models\Product;
use App\Models\Category;
use App\Services\ReportExportService;
use Livewire\Component;
use Livewire\WithPagination;

class StockInventoryReport extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $stockStatus = 'all'; // all, in_stock, low_stock, out_of_stock
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'stockStatus' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStockStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getProductsQuery()
    {
        $query = Product::with('category')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category_id', $this->categoryFilter);
            })
            ->when($this->stockStatus !== 'all', function ($q) {
                switch ($this->stockStatus) {
                    case 'out_of_stock':
                        $q->where('current_stock_quantity', 0);
                        break;
                    case 'low_stock':
                        $q->whereColumn('current_stock_quantity', '<=', 'reorder_level')
                            ->where('current_stock_quantity', '>', 0);
                        break;
                    case 'in_stock':
                        $q->whereColumn('current_stock_quantity', '>', 'reorder_level');
                        break;
                }
            });

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportToExcel()
    {
        $products = $this->getProductsQuery()->get();

        $data = $products->map(function ($product) {
            $avgCost = $product->getAverageUnitCost();
            return [
                'SKU' => $product->sku,
                'Barcode' => $product->barcode,
                'Product Name' => $product->name,
                'Category' => $product->category?->name ?? 'N/A',
                'Current Stock' => $product->current_stock_quantity,
                'Damaged Stock' => $product->damaged_stock_quantity,
                'Reorder Level' => $product->reorder_level,
                'Unit' => $product->base_unit,
                'Avg Cost Price' => number_format($avgCost, 2),
                'Min Selling Price' => number_format($product->min_selling_price, 2),
                'Max Selling Price' => number_format($product->max_selling_price, 2),
                'Stock Value' => number_format($product->current_stock_quantity * $avgCost, 2),
                'Status' => $this->getStockStatus($product),
            ];
        })->toArray();

        $headers = [
            'SKU',
            'Barcode',
            'Product Name',
            'Category',
            'Current Stock',
            'Damaged Stock',
            'Reorder Level',
            'Unit',
            'Avg Cost Price',
            'Min Selling Price',
            'Max Selling Price',
            'Stock Value',
            'Status'
        ];

        $exportService = new ReportExportService();
        return $exportService->exportToCSV($data, $headers, 'stock_inventory_report');
    }

    public function exportToPdf()
    {
        $products = $this->getProductsQuery()->get();
        $totalValue = $products->sum(function ($product) {
            return $product->current_stock_quantity * $product->getAverageUnitCost();
        });

        return response()->view('reports.pdf.stock-inventory', [
            'products' => $products,
            'totalValue' => $totalValue,
            'generatedAt' => now(),
            'filters' => [
                'search' => $this->search,
                'category' => $this->categoryFilter ? Category::find($this->categoryFilter)?->name : 'All',
                'stockStatus' => ucfirst(str_replace('_', ' ', $this->stockStatus)),
            ]
        ])->header('Content-Type', 'text/html');
    }

    private function getStockStatus($product)
    {
        if ($product->current_stock_quantity == 0) {
            return 'Out of Stock';
        } elseif ($product->current_stock_quantity <= $product->reorder_level) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function render()
    {
        $products = $this->getProductsQuery()->paginate($this->perPage);

        $categories = Category::orderBy('name')->get();

        $totalValue = $this->getProductsQuery()->get()->sum(function ($product) {
            return $product->current_stock_quantity * $product->getAverageUnitCost();
        });

        $stats = [
            'total_products' => $this->getProductsQuery()->count(),
            'total_value' => $totalValue,
            'out_of_stock' => Product::where('current_stock_quantity', 0)->count(),
            'low_stock' => Product::whereColumn('current_stock_quantity', '<=', 'reorder_level')
                ->where('current_stock_quantity', '>', 0)->count(),
        ];

        return view('livewire.report-generation.stock-inventory-report', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
