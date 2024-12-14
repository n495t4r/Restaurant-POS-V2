<x-filament-panels::page>
    <div class="mb-4">
        <h2 class="text-md">{{ now()->format('F j, Y') }}</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Pending Orders Section --}}
        <div class="bg-white rounded-lg shadow p-4" wire:poll.10s="refreshPendingOrders">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold flex items-center">
                    <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                    Pending
                    <span class="ml-2 px-2 py-1 text-xs bg-primary-100 text-primary-700 rounded-full">
                        {{ count($pendingOrders) }}
                    </span>
                </h2>
            </div>

            <div class="space-y-4">
                @forelse($pendingOrders as $order)
                <div class="bg-orange-50 rounded-lg p-4 cursor-pointer transition hover:shadow-md"
                    wire:click="$set('selectedOrder', {{ $order['id'] }}); $set('showOrderModal', true)">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-sm font-medium">Order #{{ $order['id'] }}</span>
                            <p class="text-xs text-gray-600">{{ $order['created_at'] }}</p>
                            <p class="text-xs mt-1">{{ $order['items_count'] }} items</p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click.stop="updateOrderStatus({{ $order['id'] }}, 3)"
                                class="text-xs px-2 py-1 bg-primary-600 text-white rounded hover:bg-primary-700">
                                Accept
                            </button>
                            <button wire:click.stop="updateOrderStatus({{ $order['id'] }}, 0)"
                                class="text-xs px-2 py-1 bg-danger-600 text-white rounded hover:bg-danger-700">
                                Decline
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2" />
                    <p class="text-sm">No pending orders for today</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Accepted Orders Section --}}
        <div class="bg-white rounded-lg shadow p-4" wire:poll.10s="$refresh">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold flex items-center">
                    <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                    Accepted
                    <span class="ml-2 px-2 py-1 text-xs bg-primary-100 text-primary-700 rounded-full">
                        {{ count($acceptedOrders) }}
                    </span>
                </h2>
            </div>

            <div class="space-y-4">
                @forelse($acceptedOrders as $order)
                <div class="bg-orange-50 rounded-lg p-4 cursor-pointer transition hover:shadow-md"
                    wire:click="$set('selectedOrder', {{ $order['id'] }}); $set('showOrderModal', true)">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-sm font-medium">Order #{{ $order['id'] }}</span>
                            <p class="text-xs text-gray-600">{{ $order['created_at'] }}</p>
                            <p class="text-xs mt-1">{{ $order['items_count'] }} items</p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click.stop="updateOrderStatus({{ $order['id'] }}, 1)"
                                class="text-xs px-2 py-1 bg-primary-600 text-white rounded hover:bg-primary-700">
                                Mark ready
                            </button>
                            <button wire:click.stop="updateOrderStatus({{ $order['id'] }}, 0)"
                                class="text-xs px-2 py-1 bg-danger-600 text-white rounded hover:bg-danger-700">
                                Decline
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2" />
                    <p class="text-sm">No accepted orders for today</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Ready Orders Section --}}

        <div class="bg-white rounded-lg shadow p-4" wire:poll.10s="$refresh">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold flex items-center">
                    <x-heroicon-o-clock class="w-5 h-5 mr-2" />
                    Ready
                    <span class="ml-2 px-2 py-1 text-xs bg-primary-100 text-primary-700 rounded-full">
                        {{ count($readyOrders) }}
                    </span>
                </h2>
            </div>

            <div class="space-y-4">
                @forelse($readyOrders as $order)
                <div class="bg-orange-50 rounded-lg p-4 cursor-pointer transition hover:shadow-md"
                    wire:click="$set('selectedOrder', {{ $order['id'] }}); $set('showOrderModal', true)">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-sm font-medium">Order #{{ $order['id'] }}</span>
                            <p class="text-xs text-gray-600">{{ $order['created_at'] }}</p>
                            <p class="text-xs mt-1">{{ $order['items_count'] }} items</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2" />
                    <p class="text-sm">No ready orders for today</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Order Details Modal --}}
    <x-filament::modal wire:model="showOrderModal" width="xl">
        <x-slot name="header">
            <h2 class="text-lg font-medium">
                Order Details
            </h2>
        </x-slot>

        @if($selectedOrder)
        {{-- Load and display detailed order information here --}}
        @livewire('order-details', ['orderId' => $selectedOrder])
        @endif
    </x-filament::modal>

    {{-- Audio Notification Setup --}}

    @push('scripts')
    <script>
        let notificationAudio;

        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire Initialized - Setting up event listeners');

            Livewire.on('order-created', (data) => {
                console.error('Order Created Event Caught in JS', data);
            });

            Livewire.on('register-notification-sound', ({
                url
            }) => {
                notificationAudio = new Audio(url);
            });

            Livewire.on('play-notification', () => {
                if (notificationAudio) {
                    notificationAudio.play().catch(error => console.error('Error playing audio:', error));
                }
            });

            Livewire.on('newOrderCreated', (data) => {
                console.log('New order created:', data.orderId);
            });

            // Additional global error catching
            window.addEventListener('error', (event) => {
                console.error('Global Error Caught', event);
            });

        });
    </script>
    @endpush
</x-filament-panels::page>