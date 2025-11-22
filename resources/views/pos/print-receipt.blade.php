<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .receipt-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .header .logo {
            position: absolute;
            left: 0;
            top: 0;
            max-width: 120px;
            max-height: 80px;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .header .shop-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
        }

        .header .shop-info {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .header .receipt-title {
            font-size: 1.125rem;
            font-weight: bold;
            color: #2563eb;
            margin-top: 0.5rem;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .invoice-details .label {
            color: #6b7280;
            font-weight: normal;
        }

        .invoice-details .value {
            font-weight: 600;
            color: #1f2937;
        }

        .invoice-details .invoice-number {
            font-size: 1.125rem;
            font-weight: bold;
        }

        .invoice-details .text-right {
            text-align: right;
        }

        .invoice-details .customer-phone {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        table thead tr {
            border-bottom: 2px solid #1f2937;
        }

        table th {
            padding: 0.5rem 0;
            font-weight: 600;
            text-align: left;
        }

        table th.text-center {
            text-align: center;
        }

        table th.text-right {
            text-align: right;
        }

        table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        table td {
            padding: 0.5rem 0;
        }

        table td.text-center {
            text-align: center;
        }

        table td.text-right {
            text-align: right;
        }

        .box-badge {
            font-size: 0.75rem;
            color: #2563eb;
        }

        .totals {
            border-top: 2px solid #1f2937;
            padding-top: 0.75rem;
            margin-bottom: 1rem;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .totals-row .label {
            color: #6b7280;
        }

        .totals-row .value {
            font-weight: 600;
        }

        .totals-row.discount .value {
            color: #dc2626;
        }

        .totals-row.grand-total {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #9ca3af;
        }

        .payment-details {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .payment-details h3 {
            font-size: 0.875rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .payment-details .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .payment-details .payment-row .label {
            color: #6b7280;
        }

        .payment-details .payment-row .value {
            font-weight: 600;
        }

        .payment-details .change-row {
            border-top: 1px solid #d1d5db;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            font-weight: bold;
        }

        .payment-details .change-row.positive {
            color: #059669;
        }

        .payment-details .change-row.negative {
            color: #dc2626;
        }

        .status-badge {
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            text-align: center;
            border: 2px solid;
        }

        .status-badge.unpaid {
            background-color: #fef2f2;
            border-color: #ef4444;
        }

        .status-badge.unpaid .title {
            color: #991b1b;
            font-weight: bold;
            font-size: 1.125rem;
        }

        .status-badge.unpaid .subtitle {
            color: #dc2626;
            font-size: 0.875rem;
        }

        .status-badge.partial {
            background-color: #fffbeb;
            border-color: #f59e0b;
        }

        .status-badge.partial .title {
            color: #92400e;
            font-weight: bold;
            font-size: 1.125rem;
        }

        .status-badge.partial .subtitle {
            color: #d97706;
            font-size: 0.875rem;
        }

        .status-badge.paid {
            background-color: #f0fdf4;
            border-color: #22c55e;
        }

        .status-badge.paid .title {
            color: #166534;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            border-top: 2px solid #1f2937;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .footer .thank-you {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .footer .custom-text {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .footer .powered-by {
            font-size: 0.75rem;
            color: #d1d5db;
            margin-top: 0.5rem;
        }

        .action-buttons {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 1000;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #4b5563;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #374151;
        }

        .bottom-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .bottom-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 0.75rem 1rem;
        }

        svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        @media print {
            body {
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .container {
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                padding: 1rem;
            }
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <!-- Action Buttons (No Print) -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print (Ctrl+P)
        </button>
        <button onclick="closeInvoice()" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Close Invoice
        </button>
    </div>

    <!-- Receipt Container -->
    <div class="container">
        <div class="receipt-container">
            <!-- Header -->
            <div class="header">
                <!-- Logo (Left Top) - Update path in settings or replace /images/logo.png with your logo path -->
                <img src="/images/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">

                <h1>{{ config('app.name', 'Grocery ERP') }}</h1>
                @php
                    $settings = \App\Models\Setting::first();
                @endphp
                @if($settings)
                    @if($settings->shop_name)
                        <div class="shop-name">{{ $settings->shop_name }}</div>
                    @endif
                    @if($settings->shop_address)
                        <div class="shop-info">{{ $settings->shop_address }}</div>
                    @endif
                    @if($settings->shop_phone)
                        <div class="shop-info">Tel: {{ $settings->shop_phone }}</div>
                    @endif
                @endif
                <div class="receipt-title">SALES RECEIPT</div>
            </div>

            <!-- Invoice Details -->
            <div class="invoice-details">
                <div>
                    <div class="label">Invoice Number:</div>
                    <div class="value invoice-number">{{ $sale->invoice_number }}</div>
                </div>
                <div class="text-right">
                    <div class="label">Date & Time:</div>
                    <div class="value">{{ $sale->sale_date->format('d M Y, h:i A') }}</div>
                </div>
                @if($sale->customer)
                    <div>
                        <div class="label">Customer:</div>
                        <div class="value">{{ $sale->customer->name }}</div>
                        @if($sale->customer->phone)
                            <div class="customer-phone">{{ $sale->customer->phone }}</div>
                        @endif
                    </div>
                @endif
                <div class="text-right">
                    <div class="label">Cashier:</div>
                    <div class="value">{{ $sale->cashier->name ?? 'N/A' }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Disc.</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>
                                {{ $item->product->name }}
                                @if($item->is_box_sale)
                                    <span class="box-badge">(Box)</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ number_format($item->discount_amount, 2) }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals">
                <div class="totals-row">
                    <span class="label">Subtotal:</span>
                    <span class="value">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="totals-row discount">
                        <span class="label">
                            Discount
                            @if($sale->discount_type === 'percentage')
                                ({{ $sale->discount_amount }}%)
                            @endif:
                        </span>
                        <span class="value">
                            - Rs. {{ number_format($sale->discount_type === 'percentage' ? ($sale->subtotal * $sale->discount_amount / 100) : $sale->discount_amount, 2) }}
                        </span>
                    </div>
                @endif
                <div class="totals-row grand-total">
                    <span>TOTAL:</span>
                    <span>Rs. {{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
                <h3>Payment Details:</h3>
                @foreach($sale->payments as $payment)
                    <div class="payment-row">
                        <span class="label">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}
                            @if($payment->bankAccount)
                                ({{ $payment->bankAccount->account_name }})
                            @endif:
                        </span>
                        <span class="value">Rs. {{ number_format($payment->amount, 2) }}</span>
                    </div>
                @endforeach
                @php
                    $totalPaid = $sale->payments->sum('amount');
                    $balance = $sale->total_amount - $totalPaid;
                @endphp
                @if($totalPaid > $sale->total_amount)
                    <div class="payment-row change-row positive">
                        <span>Change to Return:</span>
                        <span>Rs. {{ number_format($totalPaid - $sale->total_amount, 2) }}</span>
                    </div>
                @elseif($balance > 0)
                    <div class="payment-row change-row negative">
                        <span>Balance Due:</span>
                        <span>Rs. {{ number_format($balance, 2) }}</span>
                    </div>
                @endif
            </div>

            <!-- Payment Status Badge -->
            @if($sale->payment_status === 'unpaid')
                <div class="status-badge unpaid">
                    <div class="title">⚠ FULL CREDIT INVOICE</div>
                    <div class="subtitle">Total Due: Rs. {{ number_format($sale->total_amount, 2) }}</div>
                </div>
            @elseif($sale->payment_status === 'partial')
                <div class="status-badge partial">
                    <div class="title">⚠ CREDIT INVOICE</div>
                    <div class="subtitle">Balance Due: Rs. {{ number_format($balance, 2) }}</div>
                </div>
            @elseif($sale->payment_status === 'paid')
                <div class="status-badge paid">
                    <div class="title">✓ PAID IN FULL</div>
                </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <div class="thank-you">Thank you for your business!</div>
                @if($settings && $settings->receipt_footer_text)
                    <div class="custom-text">{{ $settings->receipt_footer_text }}</div>
                @endif
                <div class="powered-by">Powered by Grocery ERP System</div>
            </div>

            <!-- Bottom Actions (visible in preview, hidden in print) -->
            <div class="bottom-actions no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    Print Receipt
                </button>
                <button onclick="closeInvoice()" class="btn btn-secondary">
                    Close Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts -->
    <script>
        // Print on Ctrl+P
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });

        // Function to close invoice tab/window
        function closeInvoice() {
            // Try to close the window
            window.close();

            // If window.close() doesn't work (tab not opened by script),
            // redirect to POS as fallback
            setTimeout(function() {
                if (!window.closed) {
                    window.location.href = '{{ route("pos.index") }}';
                }
            }, 100);
        }
    </script>
</body>
</html>
