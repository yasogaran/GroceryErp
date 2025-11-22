<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice History Report</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8pt;
        }

        table thead {
            background-color: #333;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
        }

        table th.right {
            text-align: right;
        }

        table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table td {
            padding: 6px;
        }

        table td.right {
            text-align: right;
        }

        table tfoot {
            background: #f5f5f5;
            font-weight: bold;
        }

        .status {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .status.completed {
            background: #d4edda;
            color: #155724;
        }

        .status.partial {
            background: #fff3cd;
            color: #856404;
        }

        .status.pending {
            background: #f8d7da;
            color: #721c24;
        }

        .type-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .type-cash {
            background: #d1ecf1;
            color: #0c5460;
        }

        .type-credit {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice History Report</h1>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Period:</strong> {{ $filters['startDate'] }} to {{ $filters['endDate'] }}</p>
        <p><strong>Customer:</strong> {{ $filters['customer'] }} | <strong>Payment Status:</strong> {{ $filters['paymentStatus'] }} | <strong>Invoice Type:</strong> {{ $filters['invoiceType'] }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Total Invoices</div>
            <div class="value">{{ number_format($stats['total_invoices']) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Amount</div>
            <div class="value">Rs. {{ number_format($stats['total_amount'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Paid</div>
            <div class="value">Rs. {{ number_format($stats['total_paid'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Balance Due</div>
            <div class="value">Rs. {{ number_format($stats['total_balance'], 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Cashier</th>
                <th class="right">Items</th>
                <th class="right">Total</th>
                <th class="right">Paid</th>
                <th class="right">Balance</th>
                <th>Status</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $invoice->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $invoice->user?->name ?? 'N/A' }}</td>
                    <td class="right">{{ $invoice->saleItems->count() }}</td>
                    <td class="right">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="right">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="right">{{ number_format($invoice->balance, 2) }}</td>
                    <td>
                        <span class="status {{ $invoice->payment_status }}">{{ ucfirst($invoice->payment_status) }}</span>
                    </td>
                    <td>
                        <span class="type-badge type-{{ $invoice->payment_status === 'completed' ? 'cash' : 'credit' }}">
                            {{ $invoice->payment_status === 'completed' ? 'Cash' : 'Credit' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">Totals:</td>
                <td class="right">{{ number_format($invoices->sum('total_amount'), 2) }}</td>
                <td class="right">{{ number_format($invoices->sum('paid_amount'), 2) }}</td>
                <td class="right">{{ number_format($invoices->sum('balance'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
