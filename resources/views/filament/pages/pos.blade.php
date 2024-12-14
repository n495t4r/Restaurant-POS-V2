<x-filament-panels::page>
    <div class="grid sm:grid-cols-2 md:grid-cols-2 gap-4">
        <div class="md:col-span-1">
            <div class="mb-4">
                Write something...
            </div>
            {{ $this->table }}
        
        </div>
        <div class="flex flex-col gap-4">
            <x-filament::section heading="Cart {{ number_format($this->cartTotal, 2) }} NGN">
                @if($this->cart->count())
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($this->cart as $item)
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="text-sm">{{ $item->product->name }}</p>
                            <p class="text-sm">{{ $item->qty }} * {{ number_format(($item->price+$item->vat)-$item->discount, 2) }} NGN</p>
                        </div>
                        <div class="flex items-center text-xs">
                            <x-filament::button size="sm" wire:click="decreaseQty({{ $item->id }})">-</x-filament::button>
                            <span class="mx-2">{{ $item->qty }}</span>
                            <x-filament::button size="sm" wire:click="increaseQty({{ $item->id }})">+</x-filament::button>
                            <x-filament::icon-button icon="heroicon-s-trash" color="danger" wire:click="removeFromCart({{ $item->id }})" class="ml-2" />
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2">
                    {{ ($this->clearCart) }}
                </div>
                @else
                <div class="text-center flex justify-center items-center flex-col h-full">
                    <div class="flex justify-center items-center flex-col gap-2">
                        <x-heroicon-s-shopping-cart class="w-8 h-8" />
                        <p>Cart is empty</p>
                    </div>
                </div>
                @endif
            </x-filament::section>
            @if($this->cart->count()) 
            <x-filament::section heading="Totals">
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <div class="flex justify-between items-center py-2">
                        <p class="font-bold">Subtotal</p>
                        <p>{{ number_format($this->cartSubtotal, 2) }} NGN</p>
                    </div>
                    <div class="flex justify-between items-center py-2 text-danger-600">
                        <p class="font-bold">Discount</p>
                        <p>{{ number_format($this->cartDiscount, 2) }} NGN</p>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <p class="font-bold">VAT</p>
                        <p>{{ number_format($this->cartVat, 2) }} NGN</p>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <p class="font-bold">Total</p>
                        <p class="font-bold">{{ number_format($this->cartTotal, 2) }} NGN</p>
                    </div>
                </div>
                <div class="mt-2">
                    {{ ($this->checkoutAction)(['total' => $this->cartTotal]) }}
                </div>
            </x-filament::section>
            @endif
        </div>
    </div>


    <x-filament-actions::modals />
</x-filament-panels::page>