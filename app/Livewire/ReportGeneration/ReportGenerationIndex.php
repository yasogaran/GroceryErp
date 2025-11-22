<?php

namespace App\Livewire\ReportGeneration;

use Livewire\Component;

class ReportGenerationIndex extends Component
{
    public function render()
    {
        $reports = [
            [
                'name' => 'Stock Inventory Report',
                'description' => 'Comprehensive stock inventory with filters by category, stock status, and product search. Export to Excel/PDF.',
                'icon' => 'cube',
                'route' => 'report-generation.stock-inventory',
                'color' => 'blue',
            ],
            [
                'name' => 'Sales Report',
                'description' => 'Detailed sales analysis with date range filters, cashier filtering, and payment status. Includes profit calculations.',
                'icon' => 'chart-bar',
                'route' => 'report-generation.sales',
                'color' => 'green',
            ],
            [
                'name' => 'Accounts Report',
                'description' => 'Account activity report showing opening balance, debits, credits, and closing balance for any date range.',
                'icon' => 'calculator',
                'route' => 'report-generation.accounts',
                'color' => 'purple',
            ],
            [
                'name' => 'Invoice History',
                'description' => 'Complete invoice history with filters by customer, date range, payment status (completed, partial, pending), and invoice type.',
                'icon' => 'document-text',
                'route' => 'report-generation.invoice-history',
                'color' => 'orange',
            ],
            [
                'name' => 'Journal Entries Report',
                'description' => 'Detailed journal entries report with filters by date, status (posted, draft, reversed), and account.',
                'icon' => 'book-open',
                'route' => 'report-generation.journal-entries',
                'color' => 'indigo',
            ],
        ];

        return view('livewire.report-generation.report-generation-index', [
            'reports' => $reports,
        ]);
    }
}
