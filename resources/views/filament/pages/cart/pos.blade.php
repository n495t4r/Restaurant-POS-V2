<x-filament-pages::page>
    {{ $this->form }}

    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <div class="grid grid-cols-3 gap-4">
                @foreach ($this->products as $product)
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="font-bold">{{ $product->name }}</h3>
                        <p>{{ $product->price }}</p>
                        <x-filament::button wire:click="addToCart({{ $product->id }})" class="mt-2">
                            Add
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
            {{ $this->products->links() }}
        </div>
        <div>
            <h2 class="text-2xl font-bold mb-4">Cart</h2>
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart as $productId => $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>{{ $item['price'] * $item['quantity'] }}</td>
                            <td>
                                <x-filament::button wire:click="removeFromCart({{ $productId }})" color="danger" size="sm">
                                    Remove
                                </x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                <p class="text-xl font-bold">Total: {{ $total }}</p>
            </div>
        </div>
    </div>
</x-filament-pages::page>

Version 3 of 3