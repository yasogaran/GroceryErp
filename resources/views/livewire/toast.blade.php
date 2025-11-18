<div
    x-data="{
        messages: @entangle('messages'),
        enableSound: @entangle('enableSound'),
        audioContext: null,
        init() {
            // Initialize Web Audio API for notification sound
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        },
        playSound() {
            if (this.enableSound && this.audioContext) {
                try {
                    // Create a pleasant notification beep
                    const oscillator = this.audioContext.createOscillator();
                    const gainNode = this.audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioContext.destination);

                    // Set frequency for a pleasant tone (E note)
                    oscillator.frequency.value = 659.25;
                    oscillator.type = 'sine';

                    // Envelope for smooth sound
                    gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.3, this.audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.15);

                    // Play the sound
                    oscillator.start(this.audioContext.currentTime);
                    oscillator.stop(this.audioContext.currentTime + 0.15);
                } catch (err) {
                    console.log('Audio play failed:', err);
                }
            }
        },
        removeMessage(id) {
            $wire.removeMessage(id);
        }
    }"
    @toast-added.window="playSound()"
    class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none"
    aria-live="assertive"
>
    <template x-for="message in messages" :key="message.id">
        <div
            x-show="true"
            x-init="setTimeout(() => removeMessage(message.id), message.duration)"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
            :class="{
                'bg-green-50': message.type === 'success',
                'bg-red-50': message.type === 'error',
                'bg-yellow-50': message.type === 'warning',
                'bg-blue-50': message.type === 'info'
            }"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <svg x-show="message.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <!-- Error Icon -->
                        <svg x-show="message.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <!-- Warning Icon -->
                        <svg x-show="message.type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>

                        <!-- Info Icon -->
                        <svg x-show="message.type === 'info'" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Message Content -->
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p
                            class="text-sm font-medium"
                            :class="{
                                'text-green-800': message.type === 'success',
                                'text-red-800': message.type === 'error',
                                'text-yellow-800': message.type === 'warning',
                                'text-blue-800': message.type === 'info'
                            }"
                            x-text="message.message"
                        ></p>
                    </div>

                    <!-- Close Button -->
                    <div class="ml-4 flex flex-shrink-0">
                        <button
                            @click="removeMessage(message.id)"
                            class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                            :class="{
                                'text-green-500 hover:text-green-600 focus:ring-green-500': message.type === 'success',
                                'text-red-500 hover:text-red-600 focus:ring-red-500': message.type === 'error',
                                'text-yellow-500 hover:text-yellow-600 focus:ring-yellow-500': message.type === 'warning',
                                'text-blue-500 hover:text-blue-600 focus:ring-blue-500': message.type === 'info'
                            }"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
