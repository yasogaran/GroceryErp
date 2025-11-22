<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Inventory Report</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
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
            color: #333;
        }

        .header p {
            margin: 3px 0;
            font-size: 9pt;
            color: #666;
        }

        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 3px;
        }

        .filters p {
            margin: 3px 0;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #333;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        table th.right {
            text-align: right;
        }

        table th.center {
            text-align: center;
        }

        table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table td {
            padding: 6px;
            font-size: 8pt;
        }

        table td.right {
            text-align: right;
        }

        table td.center {
            text-align: center;
        }

        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .status.in-stock {
            background-color: #d4edda;
            color: #155724;
        }

        .status.low-stock {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 3px;
        }

        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 12pt;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 3px;
        }

        .summary-item .value {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Inventory Report</h1>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Filters Applied:</strong></p>
        @if($filters['search'])
            <p>Search: {{ $filters['search'] }}</p>
        @endif
        <p>Category: {{ $filters['category'] }}</p>
        <p>Stock Status: {{ $filters['stockStatus'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Barcode</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="right">Stock</th>
                <th class="right">Min/Max</th>
                <th class="right">Cost</th>
                <th class="right">Selling</th>
                <th class="right">Value</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name ?? 'N/A' }}</td>
                    <td class="right">{{ number_format($product->quantity, 2) }} {{ $product->unit }}</td>
                    <td class="right">{{ $product->minimum_quantity }} / {{ $product->maximum_quantity ?? 'N/A' }}</td>
                    <td class="right">{{ number_format($product->cost_price, 2) }}</td>
                    <td class="right">{{ number_format($product->selling_price, 2) }}</td>
                    <td class="right">{{ number_format($product->quantity * $product->cost_price, 2) }}</td>
                    <td class="center">
                        @if($product->quantity == 0)
                            <span class="status out-of-stock">Out of Stock</span>
                        @elseif($product->quantity <= $product->minimum_quantity)
                            <span class="status low-stock">Low Stock</span>
                        @else
                            <span class="status in-stock">In Stock</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Products</div>
                <div class="value">{{ number_format($products->count()) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Stock Value</div>
                <div class="value">Rs. {{ number_format($totalValue, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Average Value/Product</div>
                <div class="value">Rs. {{ $products->count() > 0 ? number_format($totalValue / $products->count(), 2) : '0.00' }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated report. Press Ctrl+P to print or save as PDF.</p>
    </div>

    <script>
        // Auto-print when loaded (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
