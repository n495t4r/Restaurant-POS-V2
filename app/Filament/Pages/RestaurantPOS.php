<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Filament\Actions\Action;
use App\Models\Product;
use App\Models\Customer;

class RestaurantPOS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static bool $shouldRegisterNavigation = false;

    public $searchQuery = '';
    public $cart = [];
    public $total = 0;
    public $orderChannel;
    public $customerId;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('orderChannel')
                    ->options([
                        'dine-in' => 'Dine In',
                        'takeout' => 'Takeout',
                        'delivery' => 'Delivery',
                    ])
                    ->required(),
                Select::make('customerId')
                    ->label('Customer')
                    ->options(Customer::pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('searchQuery')
                    ->placeholder('Search products...')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function () {
                        $this->resetPage();
                    }),
            ]);
    }

    public function getProductsProperty()
    {
        return Product::where('name', 'like', "%{$this->searchQuery}%")->paginate(10);
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }

        $this->calculateTotal();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function emptyCart()
    {
        $this->cart = [];
        $this->total = 0;
    }

    public function submitOrder()
    {
        // Logic to submit the order
        // Create an Order record, associate OrderItems, etc.
    }

    protected function getActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit Order')
                ->action('submitOrder'),
            Action::make('empty')
                ->label('Empty Cart')
                ->color('secondary')
                ->action('emptyCart'),
        ];
    }
}
