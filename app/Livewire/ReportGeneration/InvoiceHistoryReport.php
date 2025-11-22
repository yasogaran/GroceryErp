<?php

namespace App\Livewire\ReportGeneration;

use App\Models\Sale;
use App\Models\Customer;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceHistoryReport extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate;
    public $endDate;
    public $customerFilter = '';
    public $paymentStatus = 'all'; // all, completed, partial, pending
    public $invoiceType = 'all'; // all, cash, credit
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'customerFilter' => ['except' => ''],
        'paymentStatus' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus()
    {
        $this->resetPage();
    }

    public function updatingInvoiceType()
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

    public function getInvoicesQuery()
    {
        $query = Sale::with(['user', 'customer', 'saleItems'])
            ->when($this->search, function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->startDate, function ($q) {
                $q->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->customerFilter, function ($q) {
                $q->where('customer_id', $this->customerFilter);
            })
            ->when($this->paymentStatus !== 'all', function ($q) {
                $q->where('payment_status', $this->paymentStatus);
            })
            ->when($this->invoiceType !== 'all', function ($q) {
                if ($this->invoiceType === 'cash') {
                    $q->where('payment_status', 'completed');
                } else {
                    $q->whereIn('payment_status', ['partial', 'pending']);
                }
            });

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportToExcel()
    {
        $invoices = $this->getInvoicesQuery()->get();

        $data = $invoices->map(function ($invoice) {
            return [
                'Invoice #' => $invoice->invoice_number,
                'Date' => $invoice->created_at->format('Y-m-d H:i:s'),
                'Customer' => $invoice->customer?->name ?? 'Walk-in Customer',
                'Customer Phone' => $invoice->customer?->phone ?? 'N/A',
                'Cashier' => $invoice->user?->name ?? 'N/A',
                'Items Count' => $invoice->saleItems->count(),
                'Subtotal' => number_format($invoice->subtotal, 2),
                'Discount' => number_format($invoice->discount_amount, 2),
                'Tax' => number_format($invoice->tax_amount, 2),
                'Total Amount' => number_format($invoice->total_amount, 2),
                'Paid Amount' => number_format($invoice->paid_amount, 2),
                'Balance Due' => number_format($invoice->balance, 2),
                'Payment Status' => ucfirst($invoice->payment_status),
                'Invoice Type' => $invoice->payment_status === 'completed' ? 'Cash Invoice' : 'Credit Invoice',
            ];
        })->toArray();

        $headers = [
            'Invoice #', 'Date', 'Customer', 'Customer Phone', 'Cashier',
            'Items Count', 'Subtotal', 'Discount', 'Tax', 'Total Amount',
            'Paid Amount', 'Balance Due', 'Payment Status', 'Invoice Type'
        ];

        $exportService = new ReportExportService();
        return $exportService->exportToCSV($data, $headers, 'invoice_history_report');
    }

    public function exportToPdf()
    {
        $invoices = $this->getInvoicesQuery()->get();

        $stats = $this->calculateStats();

        return response()->view('reports.pdf.invoice-history', [
            'invoices' => $invoices,
            'stats' => $stats,
            'generatedAt' => now(),
            'filters' => [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'customer' => $this->customerFilter ? Customer::find($this->customerFilter)?->name : 'All',
                'paymentStatus' => ucfirst($this->paymentStatus),
                'invoiceType' => ucfirst($this->invoiceType),
            ]
        ])->header('Content-Type', 'text/html');
    }

    private function calculateStats()
    {
        $invoices = $this->getInvoicesQuery()->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('paid_amount'),
            'total_balance' => $invoices->sum('balance'),
            'completed_invoices' => $invoices->where('payment_status', 'completed')->count(),
            'credit_invoices' => $invoices->whereIn('payment_status', ['partial', 'pending'])->count(),
            'average_invoice' => $invoices->count() > 0 ? $invoices->sum('total_amount') / $invoices->count() : 0,
        ];
    }

    public function render()
    {
        $invoices = $this->getInvoicesQuery()->paginate($this->perPage);
        $customers = Customer::orderBy('name')->get();
        $stats = $this->calculateStats();

        return view('livewire.report-generation.invoice-history-report', [
            'invoices' => $invoices,
            'customers' => $customers,
            'stats' => $stats,
        ]);
    }
}
