<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: true,
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .receipt-container {
                max-width: 100%;
                margin: 0;
                box-shadow: none;
            }
        }
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Action Buttons (No Print) -->
    <div class="no-print fixed top-4 right-4 flex gap-2 z-50">
        <button
            onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Receipt (Ctrl+P)
        </button>
        <a
            href="{{ route('pos.index') }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to POS
        </a>
    </div>

    <!-- Receipt Container -->
    <div class="flex justify-center items-start min-h-screen py-8">
        <div class="receipt-container bg-white shadow-2xl rounded-lg p-8 max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center border-b-2 border-gray-800 pb-4 mb-4">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ config('app.name', 'Grocery ERP') }}</h1>
                @php
                    $settings = \App\Models\Setting::first();
                @endphp
                @if($settings)
                    @if($settings->shop_name)
                        <p class="text-xl font-semibold text-gray-700">{{ $settings->shop_name }}</p>
                    @endif
                    @if($settings->shop_address)
                        <p class="text-sm text-gray-600">{{ $settings->shop_address }}</p>
                    @endif
                    @if($settings->shop_phone)
                        <p class="text-sm text-gray-600">Tel: {{ $settings->shop_phone }}</p>
                    @endif
                @endif
                <p class="text-lg font-bold text-blue-600 mt-2">SALES RECEIPT</p>
            </div>

            <!-- Invoice Details -->
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <p class="text-gray-600">Invoice Number:</p>
                    <p class="font-bold text-lg">{{ $sale->invoice_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600">Date & Time:</p>
                    <p class="font-semibold">{{ $sale->sale_date->format('d M Y, h:i A') }}</p>
                </div>
                @if($sale->customer)
                    <div>
                        <p class="text-gray-600">Customer:</p>
                        <p class="font-semibold">{{ $sale->customer->name }}</p>
                        @if($sale->customer->phone)
                            <p class="text-xs text-gray-500">{{ $sale->customer->phone }}</p>
                        @endif
                    </div>
                @endif
                <div class="{{ $sale->customer ? '' : 'col-span-2' }} text-right">
                    <p class="text-gray-600">Cashier:</p>
                    <p class="font-semibold">{{ $sale->cashier->name ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-800">
                            <th class="text-left py-2">Item</th>
                            <th class="text-center py-2">Qty</th>
                            <th class="text-right py-2">Price</th>
                            <th class="text-right py-2">Disc.</th>
                            <th class="text-right py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr class="border-b border-gray-200">
                                <td class="py-2">
                                    {{ $item->product->name }}
                                    @if($item->is_box_sale)
                                        <span class="text-xs text-blue-600">(Box)</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">{{ number_format($item->discount_amount, 2) }}</td>
                                <td class="text-right font-semibold">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="border-t-2 border-gray-800 pt-3 mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">
                            Discount
                            @if($sale->discount_type === 'percentage')
                                ({{ $sale->discount_amount }}%)
                            @endif:
                        </span>
                        <span class="font-semibold text-red-600">
                            - Rs. {{ number_format($sale->discount_type === 'percentage' ? ($sale->subtotal * $sale->discount_amount / 100) : $sale->discount_amount, 2) }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between text-xl font-bold mt-2 pt-2 border-t border-gray-400">
                    <span>TOTAL:</span>
                    <span>Rs. {{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <h3 class="font-bold text-sm mb-2">Payment Details:</h3>
                @foreach($sale->payments as $payment)
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}
                            @if($payment->bankAccount)
                                ({{ $payment->bankAccount->account_name }})
                            @endif:
                        </span>
                        <span class="font-semibold">Rs. {{ number_format($payment->amount, 2) }}</span>
                    </div>
                @endforeach
                @php
                    $totalPaid = $sale->payments->sum('amount');
                    $balance = $sale->total_amount - $totalPaid;
                @endphp
                @if($totalPaid > $sale->total_amount)
                    <div class="flex justify-between text-sm font-bold text-green-600 mt-2 pt-2 border-t border-gray-300">
                        <span>Change to Return:</span>
                        <span>Rs. {{ number_format($totalPaid - $sale->total_amount, 2) }}</span>
                    </div>
                @elseif($balance > 0)
                    <div class="flex justify-between text-sm font-bold text-red-600 mt-2 pt-2 border-t border-gray-300">
                        <span>Balance Due:</span>
                        <span>Rs. {{ number_format($balance, 2) }}</span>
                    </div>
                @endif
            </div>

            <!-- Payment Status Badge -->
            @if($sale->payment_status === 'unpaid')
                <div class="bg-red-50 border-2 border-red-500 rounded-lg p-3 mb-4 text-center">
                    <p class="text-red-800 font-bold text-lg">⚠ FULL CREDIT INVOICE</p>
                    <p class="text-red-700 text-sm">Total Due: Rs. {{ number_format($sale->total_amount, 2) }}</p>
                </div>
            @elseif($sale->payment_status === 'partial')
                <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-3 mb-4 text-center">
                    <p class="text-yellow-800 font-bold text-lg">⚠ CREDIT INVOICE</p>
                    <p class="text-yellow-700 text-sm">Balance Due: Rs. {{ number_format($balance, 2) }}</p>
                </div>
            @elseif($sale->payment_status === 'paid')
                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-3 mb-4 text-center">
                    <p class="text-green-800 font-bold">✓ PAID IN FULL</p>
                </div>
            @endif

            <!-- Footer -->
            <div class="text-center border-t-2 border-gray-800 pt-4 mt-4">
                <p class="text-sm text-gray-600 mb-1">Thank you for your business!</p>
                @if($settings && $settings->receipt_footer_text)
                    <p class="text-xs text-gray-500">{{ $settings->receipt_footer_text }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-2">Powered by Grocery ERP System</p>
            </div>

            <!-- Print Button (visible in preview, hidden in print) -->
            <div class="no-print mt-6 flex gap-3">
                <button
                    onclick="window.print()"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold">
                    Print Receipt
                </button>
                <a
                    href="{{ route('pos.index') }}"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-bold text-center">
                    Back to POS
                </a>
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

        // Auto-print on load (optional - commented out)
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 500);
        // });
    </script>
</body>
</html>
