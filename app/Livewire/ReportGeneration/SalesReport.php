<?php

namespace App\Livewire\ReportGeneration;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SalesReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $cashierFilter = '';
    public $paymentStatus = 'all'; // all, completed, partial, pending
    public $perPage = 25;
    public $reportType = 'summary'; // summary, detailed

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'cashierFilter' => ['except' => ''],
        'paymentStatus' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingCashierFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus()
    {
        $this->resetPage();
    }

    public function getSalesQuery()
    {
        $query = Sale::with(['cashier', 'customer', 'items.product'])
            ->when($this->startDate, function ($q) {
                $q->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->cashierFilter, function ($q) {
                $q->where('created_by', $this->cashierFilter);
            })
            ->when($this->paymentStatus !== 'all', function ($q) {
                $q->where('payment_status', $this->paymentStatus);
            });

        return $query->orderBy('created_at', 'desc');
    }

    public function exportToExcel()
    {
        $sales = $this->getSalesQuery()->get();

        if ($this->reportType === 'summary') {
            $data = $sales->map(function ($sale) {
                return [
                    'Invoice #' => $sale->invoice_number,
                    'Date' => $sale->created_at->format('Y-m-d H:i:s'),
                    'Customer' => $sale->customer?->name ?? 'Walk-in Customer',
                    'Cashier' => $sale->cashier?->name ?? 'N/A',
                    'Items Count' => $sale->items->count(),
                    'Subtotal' => number_format($sale->subtotal, 2),
                    'Discount' => number_format($sale->discount_amount, 2),
                    'Total' => number_format($sale->total_amount, 2),
                    'Paid' => number_format($sale->paid_amount, 2),
                    'Balance' => number_format($sale->due_amount, 2),
                    'Payment Status' => ucfirst($sale->payment_status),
                    'Profit' => number_format($sale->items->sum('profit'), 2),
                ];
            })->toArray();

            $headers = [
                'Invoice #', 'Date', 'Customer', 'Cashier', 'Items Count',
                'Subtotal', 'Discount', 'Total', 'Paid', 'Balance',
                'Payment Status', 'Profit'
            ];
        } else {
            // Detailed report with line items
            $data = [];
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $data[] = [
                        'Invoice #' => $sale->invoice_number,
                        'Date' => $sale->created_at->format('Y-m-d H:i:s'),
                        'Customer' => $sale->customer?->name ?? 'Walk-in Customer',
                        'Cashier' => $sale->cashier?->name ?? 'N/A',
                        'Product' => $item->product?->name ?? 'N/A',
                        'SKU' => $item->product?->sku ?? 'N/A',
                        'Quantity' => number_format($item->quantity, 2),
                        'Unit Price' => number_format($item->unit_price, 2),
                        'Subtotal' => number_format($item->subtotal, 2),
                        'Discount' => number_format($item->discount_amount, 2),
                        'Total' => number_format($item->total, 2),
                        'Cost' => number_format($item->unit_cost, 2),
                        'Profit' => number_format($item->profit, 2),
                    ];
                }
            }

            $headers = [
                'Invoice #', 'Date', 'Customer', 'Cashier', 'Product', 'SKU',
                'Quantity', 'Unit Price', 'Subtotal', 'Discount', 'Total',
                'Cost', 'Profit'
            ];
        }

        $exportService = new ReportExportService();
        return $exportService->exportToCSV($data, $headers, 'sales_report_' . $this->reportType);
    }

    public function exportToPdf()
    {
        $sales = $this->getSalesQuery()->get();

        $stats = $this->calculateStats();

        return response()->view('reports.pdf.sales-report', [
            'sales' => $sales,
            'stats' => $stats,
            'reportType' => $this->reportType,
            'generatedAt' => now(),
            'filters' => [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'cashier' => $this->cashierFilter ? User::find($this->cashierFilter)?->name : 'All',
                'paymentStatus' => ucfirst($this->paymentStatus),
            ]
        ])->header('Content-Type', 'text/html');
    }

    private function calculateStats()
    {
        $sales = $this->getSalesQuery()->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_paid' => $sales->sum(function ($sale) {
                return $sale->paid_amount;
            }),
            'total_balance' => $sales->sum(function ($sale) {
                return $sale->due_amount;
            }),
            'total_discount' => $sales->sum('discount_amount'),
            'total_profit' => $sales->sum(function ($sale) {
                return $sale->items->sum('profit');
            }),
            'average_sale' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'items_sold' => $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            }),
        ];
    }

    public function render()
    {
        $sales = $this->getSalesQuery()->paginate($this->perPage);
        $cashiers = User::whereIn('role', ['cashier', 'admin', 'manager'])->orderBy('name')->get();
        $stats = $this->calculateStats();

        return view('livewire.report-generation.sales-report', [
            'sales' => $sales,
            'cashiers' => $cashiers,
            'stats' => $stats,
        ]);
    }
}
