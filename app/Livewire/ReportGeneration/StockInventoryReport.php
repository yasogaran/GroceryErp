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
                        $q->where('quantity', 0);
                        break;
                    case 'low_stock':
                        $q->whereColumn('quantity', '<=', 'minimum_quantity')
                            ->where('quantity', '>', 0);
                        break;
                    case 'in_stock':
                        $q->whereColumn('quantity', '>', 'minimum_quantity');
                        break;
                }
            });

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportToExcel()
    {
        $products = $this->getProductsQuery()->get();

        $data = $products->map(function ($product) {
            return [
                'SKU' => $product->sku,
                'Barcode' => $product->barcode,
                'Product Name' => $product->name,
                'Category' => $product->category?->name ?? 'N/A',
                'Current Stock' => $product->quantity,
                'Minimum Stock' => $product->minimum_quantity,
                'Maximum Stock' => $product->maximum_quantity ?? 'N/A',
                'Unit' => $product->unit,
                'Cost Price' => number_format($product->cost_price, 2),
                'Selling Price' => number_format($product->selling_price, 2),
                'Stock Value' => number_format($product->quantity * $product->cost_price, 2),
                'Status' => $this->getStockStatus($product),
            ];
        })->toArray();

        $headers = [
            'SKU',
            'Barcode',
            'Product Name',
            'Category',
            'Current Stock',
            'Minimum Stock',
            'Maximum Stock',
            'Unit',
            'Cost Price',
            'Selling Price',
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
            return $product->quantity * $product->cost_price;
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
        if ($product->quantity == 0) {
            return 'Out of Stock';
        } elseif ($product->quantity <= $product->minimum_quantity) {
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
            return $product->quantity * $product->cost_price;
        });

        $stats = [
            'total_products' => $this->getProductsQuery()->count(),
            'total_value' => $totalValue,
            'out_of_stock' => Product::where('quantity', 0)->count(),
            'low_stock' => Product::whereColumn('quantity', '<=', 'minimum_quantity')
                ->where('quantity', '>', 0)->count(),
        ];

        return view('livewire.report-generation.stock-inventory-report', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
