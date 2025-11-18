<?php

namespace App\Livewire\Dashboards;

use App\Models\GRN;
use App\Models\JournalEntry;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

class AccountantDashboard extends Component
{
    public $todayRevenue;
    public $monthRevenue;
    public $todayExpenses;
    public $monthExpenses;
    public $supplierOutstanding;
    public $customerOutstanding;
    public $pendingJournalEntries;
    public $unpaidGRNs;
    public $recentPayments = [];
    public $recentJournalEntries = [];
    public $unpaidGRNsList = [];

    #[Layout('components.layouts.app')]
    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        // Revenue Metrics
        $this->todayRevenue = Sale::whereDate('created_at', today())->sum('total_amount');
        $this->monthRevenue = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Expense Metrics (GRN-based purchases)
        $this->todayExpenses = GRN::where('status', 'approved')
            ->whereDate('approved_at', today())
            ->sum('total_amount');
        $this->monthExpenses = GRN::where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('total_amount');

        // Outstanding Balances
        $this->supplierOutstanding = Supplier::sum('outstanding_balance');

        // Customer outstanding (unpaid sales)
        $this->customerOutstanding = Sale::where('payment_status', '!=', 'paid')
            ->sum(DB::raw('total_amount - (SELECT COALESCE(SUM(amount), 0) FROM sale_payments WHERE sale_id = sales.id)'));

        // Pending Journal Entries
        $this->pendingJournalEntries = JournalEntry::where('status', 'draft')->count();

        // Unpaid GRNs
        $this->unpaidGRNs = GRN::where('status', 'approved')
            ->where(function($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhere('payment_status', 'partially_paid')
                    ->orWhereNull('payment_status');
            })
            ->count();

        // Recent Supplier Payments
        $this->recentPayments = SupplierPayment::with('supplier')
            ->latest()
            ->limit(10)
            ->get();

        // Recent Journal Entries
        $this->recentJournalEntries = JournalEntry::with('creator')
            ->latest()
            ->limit(8)
            ->get();

        // Unpaid GRNs List
        $this->unpaidGRNsList = GRN::with('supplier')
            ->where('status', 'approved')
            ->where(function($query) {
                $query->where('payment_status', 'unpaid')
                    ->orWhere('payment_status', 'partially_paid')
                    ->orWhereNull('payment_status');
            })
            ->orderBy('grn_date', 'asc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.accountant-dashboard');
    }
}
