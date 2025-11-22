<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18pt;
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

        .stat-box .label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-box .value {
            font-size: 16pt;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report - {{ ucfirst($reportType) }}</h1>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Period:</strong> {{ $filters['startDate'] }} to {{ $filters['endDate'] }}</p>
        <p><strong>Cashier:</strong> {{ $filters['cashier'] }} | <strong>Payment Status:</strong> {{ $filters['paymentStatus'] }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Total Sales</div>
            <div class="value">{{ number_format($stats['total_sales']) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Revenue</div>
            <div class="value">Rs. {{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Profit</div>
            <div class="value">Rs. {{ number_format($stats['total_profit'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Average Sale</div>
            <div class="value">Rs. {{ number_format($stats['average_sale'], 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date/Time</th>
                <th>Customer</th>
                <th>Cashier</th>
                <th class="right">Items</th>
                <th class="right">Subtotal</th>
                <th class="right">Discount</th>
                <th class="right">Total</th>
                <th class="right">Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->user?->name ?? 'N/A' }}</td>
                    <td class="right">{{ $sale->saleItems->count() }}</td>
                    <td class="right">{{ number_format($sale->subtotal, 2) }}</td>
                    <td class="right">{{ number_format($sale->discount_amount, 2) }}</td>
                    <td class="right">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="right">{{ number_format($sale->saleItems->sum('profit'), 2) }}</td>
                    <td>
                        <span class="status {{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">Totals:</td>
                <td class="right">{{ number_format($sales->sum('subtotal'), 2) }}</td>
                <td class="right">{{ number_format($sales->sum('discount_amount'), 2) }}</td>
                <td class="right">{{ number_format($sales->sum('total_amount'), 2) }}</td>
                <td class="right">{{ number_format($sales->sum(fn($s) => $s->saleItems->sum('profit')), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
