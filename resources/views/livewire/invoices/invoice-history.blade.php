<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Invoice History</h1>
        <p class="text-gray-600">View and manage all sales invoices</p>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Invoice, Customer, Phone..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                    wire:model.live="filterStatus"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial (Credit)</option>
                    <option value="unpaid">Unpaid (Full Credit)</option>
                </select>
            </div>

            <!-- Cashier Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cashier</label>
                <select
                    wire:model.live="filterCashier"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                    <option value="">All Cashiers</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Clear Filters Button -->
        <div class="mt-4">
            <button
                wire:click="clearFilters"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Split View: List + Detail -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Invoice List -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">
                    Invoices
                    <span class="text-sm text-gray-500 font-normal">({{ $invoices->total() }} total)</span>
                </h2>
            </div>

            <div class="overflow-y-auto" style="max-height: calc(100vh - 400px);">
                @forelse($invoices as $invoice)
                    @php
                        $totalPaid = $invoice->payments->sum('amount');
                        $balance = $invoice->total_amount - $totalPaid;
                    @endphp
                    <div
                        wire:click="selectInvoice({{ $invoice->id }})"
                        class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition {{ $selectedInvoiceId == $invoice->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-bold text-gray-800">{{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $invoice->sale_date->format('d M Y, h:i A') }}
                                </p>
                            </div>
                            <div class="text-right">
                                @if($invoice->payment_status === 'paid')
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        PAID
                                    </span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        PARTIAL
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        UNPAID
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <div>
                                @if($invoice->customer)
                                    <p class="text-gray-700">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ $invoice->customer->name }}
                                    </p>
                                @else
                                    <p class="text-gray-500 italic">Walk-in Customer</p>
                                @endif
                                <p class="text-gray-500 text-xs">
                                    By: {{ $invoice->cashier->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800">Rs. {{ number_format($invoice->total_amount, 2) }}</p>
                                @if($balance > 0)
                                    <p class="text-xs text-red-600">Due: Rs. {{ number_format($balance, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No invoices found</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>
        </div>

        <!-- Right: Invoice Detail View -->
        <div class="bg-white rounded-lg shadow-sm">
            @if($selectedInvoice)
                @php
                    $totalPaid = $selectedInvoice->payments->sum('amount');
                    $balance = $selectedInvoice->total_amount - $totalPaid;
                @endphp

                <!-- Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $selectedInvoice->invoice_number }}</h2>
                            <p class="text-gray-600">{{ $selectedInvoice->sale_date->format('d M Y, h:i A') }}</p>
                        </div>
                        <div>
                            @if($selectedInvoice->payment_status === 'paid')
                                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full bg-green-100 text-green-800">
                                    ✓ PAID IN FULL
                                </span>
                            @elseif($selectedInvoice->payment_status === 'partial')
                                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full bg-yellow-100 text-yellow-800">
                                    ⚠ CREDIT INVOICE
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full bg-red-100 text-red-800">
                                    ⚠ FULL CREDIT
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Info -->
                    @if($selectedInvoice->customer)
                        <div class="bg-blue-50 rounded-lg p-3 mb-4">
                            <p class="text-sm text-gray-600">Customer</p>
                            <p class="font-semibold text-gray-800">{{ $selectedInvoice->customer->name }}</p>
                            @if($selectedInvoice->customer->phone)
                                <p class="text-sm text-gray-600">{{ $selectedInvoice->customer->phone }}</p>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a
                            href="{{ route('pos.receipt.print', $selectedInvoice->id) }}"
                            target="_blank"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </a>
                        @if($balance > 0)
                            <button
                                wire:click="openPaymentModal"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Make Payment
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="overflow-y-auto" style="max-height: calc(100vh - 550px);">
                    <!-- Items -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-3">Items</h3>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2">Product</th>
                                    <th class="text-center py-2">Qty</th>
                                    <th class="text-right py-2">Price</th>
                                    <th class="text-right py-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedInvoice->items as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2">
                                            {{ $item->product->name }}
                                            @if($item->is_box_sale)
                                                <span class="text-xs text-blue-600">(Box)</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right font-semibold">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold">Rs. {{ number_format($selectedInvoice->subtotal, 2) }}</span>
                            </div>
                            @if($selectedInvoice->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-semibold text-red-600">- Rs. {{ number_format($selectedInvoice->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-300">
                                <span>Total:</span>
                                <span>Rs. {{ number_format($selectedInvoice->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Payment History</h3>
                        @if($selectedInvoice->payments->count() > 0)
                            <div class="space-y-2">
                                @foreach($selectedInvoice->payments as $payment)
                                    <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}
                                            </p>
                                            @if($payment->bankAccount)
                                                <p class="text-xs text-gray-600">{{ $payment->bankAccount->account_name }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-bold text-green-600">Rs. {{ number_format($payment->amount, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Summary -->
                                <div class="bg-blue-50 rounded-lg p-3 mt-3">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm text-gray-700">Total Paid:</span>
                                        <span class="font-bold text-gray-800">Rs. {{ number_format($totalPaid, 2) }}</span>
                                    </div>
                                    @if($balance > 0)
                                        <div class="flex justify-between">
                                            <span class="text-sm font-bold text-red-600">Balance Due:</span>
                                            <span class="font-bold text-red-600">Rs. {{ number_format($balance, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="flex justify-between">
                                            <span class="text-sm font-bold text-green-600">Status:</span>
                                            <span class="font-bold text-green-600">Fully Paid</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                <p class="text-red-700 font-semibold">No Payments Recorded</p>
                                <p class="text-sm text-red-600">This is a full credit invoice</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- No Invoice Selected -->
                <div class="flex items-center justify-center h-full p-12">
                    <div class="text-center text-gray-400">
                        <svg class="w-24 h-24 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-lg">Select an invoice to view details</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal && $selectedInvoice)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Record Payment</h2>

                @php
                    $remainingBalance = $selectedInvoice->total_amount - $selectedInvoice->payments->sum('amount');
                @endphp

                <!-- Invoice Info -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600">Invoice Number</p>
                    <p class="font-bold text-lg text-gray-800">{{ $selectedInvoice->invoice_number }}</p>
                    <p class="text-sm text-gray-600 mt-2">Remaining Balance</p>
                    <p class="font-bold text-xl text-red-600">Rs. {{ number_format($remainingBalance, 2) }}</p>
                </div>

                <!-- Payment Amount -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                    <input
                        type="number"
                        wire:model="paymentAmount"
                        class="w-full text-2xl text-center border-2 border-gray-300 rounded-lg px-4 py-3"
                        placeholder="0.00"
                        step="0.01"
                        max="{{ $remainingBalance }}"
                    >
                    @error('paymentAmount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quick Amounts -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    @php
                        $quickAmounts = [100, 500, 1000];
                        if ($remainingBalance < 1000) {
                            $quickAmounts = [100, 500];
                        }
                    @endphp
                    @foreach($quickAmounts as $amount)
                        @if($amount <= $remainingBalance)
                            <button
                                wire:click="$set('paymentAmount', {{ $amount }})"
                                class="bg-gray-200 hover:bg-gray-300 py-2 rounded font-medium text-sm">
                                Rs. {{ $amount }}
                            </button>
                        @endif
                    @endforeach
                    <button
                        wire:click="$set('paymentAmount', {{ $remainingBalance }})"
                        class="col-span-{{ count($quickAmounts) == 2 ? '1' : '3' }} bg-blue-500 hover:bg-blue-600 text-white py-2 rounded font-bold text-sm">
                        Full Amount
                    </button>
                </div>

                <!-- Payment Mode -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                wire:model.live="paymentMode"
                                value="cash"
                                class="form-radio"
                            >
                            <span class="ml-2">Cash</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                wire:model.live="paymentMode"
                                value="bank_transfer"
                                class="form-radio"
                            >
                            <span class="ml-2">Bank Transfer</span>
                        </label>
                    </div>
                </div>

                <!-- Bank Account (if bank transfer) -->
                @if($paymentMode === 'bank_transfer')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account</label>
                        <select
                            wire:model="paymentBankAccount"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Select Bank Account</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('paymentBankAccount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button
                        wire:click="$set('showPaymentModal', false)"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-bold">
                        Cancel
                    </button>
                    <button
                        wire:click="recordPayment"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Record Payment</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
