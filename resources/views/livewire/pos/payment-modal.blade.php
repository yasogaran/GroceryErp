@if($show)
    <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 w-full max-w-2xl">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Payment</h2>
                <p class="text-6xl font-bold text-blue-600">
                    Rs. {{ number_format($grandTotal, 2) }}
                </p>
            </div>

            <!-- Cash Received Input -->
            <div class="mb-6">
                <label class="block text-lg font-medium text-gray-700 mb-3">Cash Received</label>
                <input
                    type="number"
                    wire:model.live="cashReceived"
                    class="w-full text-4xl text-center border-2 border-gray-300 rounded-lg px-4 py-6 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="0.00"
                    step="0.01"
                    min="{{ $grandTotal }}"
                    autofocus
                >
                @error('cashReceived')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quick Tender Buttons -->
            <div class="grid grid-cols-4 gap-3 mb-6">
                @foreach([500, 1000, 2000, 5000] as $amount)
                    <button
                        wire:click="quickTender({{ $amount }})"
                        class="bg-gray-200 hover:bg-gray-300 py-4 rounded-lg font-bold text-lg"
                    >
                        Rs. {{ number_format($amount) }}
                    </button>
                @endforeach
            </div>

            <!-- Change Display -->
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 mb-6 text-center">
                <p class="text-lg text-gray-700 mb-2">Change</p>
                <p class="text-5xl font-bold text-green-600">
                    Rs. {{ number_format($change, 2) }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4">
                <button
                    wire:click="closeModal"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 rounded-lg text-lg"
                    wire:loading.attr="disabled"
                >
                    Cancel (ESC)
                </button>

                <button
                    wire:click="confirmPayment"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                    wire:loading.attr="disabled"
                    wire:target="confirmPayment"
                    @disabled($cashReceived < $grandTotal)
                >
                    <span wire:loading.remove wire:target="confirmPayment">
                        Confirm Payment (ENTER)
                    </span>
                    <span wire:loading wire:target="confirmPayment">
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            // ESC - Close modal
            if (e.key === 'Escape') {
                e.preventDefault();
                @this.closeModal();
            }

            // ENTER - Confirm payment
            if (e.key === 'Enter' && {{ $cashReceived ?? 0 }} >= {{ $grandTotal }}) {
                e.preventDefault();
                @this.confirmPayment();
            }
        });
    </script>
@endif
