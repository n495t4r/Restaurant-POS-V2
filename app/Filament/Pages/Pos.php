<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Traits\HasCheckout;
use App\Filament\Resources\OrderResource;
use App\Models\Cart;
use App\Models\ProductCategory;
use App\Models\OrderChannel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\View\Components\Modal;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class Pos extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasCheckout, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $title = 'Cart';
    protected static ?string $slug = 'cart';
    

    protected static string $view = 'filament.pages.pos';

    public ?string $sessionID = null;
    public ?int $channelId = null;
    public ?int $customerId = null;
    public $showCheckoutModal = false;

    public function mount()
    {
        if (!session()->has('sessionID')) {
            session()->put('sessionID', Str::uuid());
        }
        $this->sessionID = session()->get('sessionID');

        if (StockHistories::isCashierUnitClosed()) {

            Notification::make()
                ->title('Cashier Unit Closed')
                ->body('The cashier unit is currently closed. Checkouts are not allowed.')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('orders')
                ->label('POS Orders')
                ->icon('heroicon-o-clipboard')
                ->url(OrderResource::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('status', true))
            ->defaultSort('counter', 'desc')
            ->paginated([12, 6, 18, 24, 30])
            ->contentGrid([
                'xs' => 2,
                'sm' => 3,
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('price')
                        ->money('NGN')
                        ->sortable(),
                    TextColumn::make('product_category.name')
                        ->label('Category')
                        ->toggleable(isToggledHiddenByDefault: true)
                        ->visible(false)
                        ->sortable(),
                    TextColumn::make('counter')
                        ->visible(false)
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->multiple()
                    ->options(ProductCategory::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Action::make('addToCart')
                    ->label('Add to Cart')
                    ->icon('heroicon-s-shopping-cart')
                    ->action(function (Product $record) {

                        // if($record->quantity) {
                            $this->addToCart($record);
                        // }else{
                        //     $this->notify($record->name.' is out of stock', 'warning');
                        // }

                    }),
            ])
            ->bulkActions([])
            ->emptyStateActions([]);
    }

    public function addToCart(Product $product)
    {
        $cartqty = Cart::where('product_id', $product->id)
            ->where('session_id', $this->sessionID)
            ->pluck('qty')->first();

        if ($product->quantity == 0 || ($cartqty +1) > $product->quantity) {
            $this->notify($product->name.' is out of stock', 'warning');
            return;
        }

        $cart = Cart::firstOrCreate(
            [
                'session_id' => $this->sessionID,
                'product_id' => $product->id,
            ],
            [
                'qty' => 0,
                'price' => $product->price,
                'discount' => 0,
                'vat' => 0,
                'total' => $product->price
            ]
        );
        
        $cart->qty += 1;
        $cart->total = $cart->price * $cart->qty;
        $cart->save();

        // $this->notify('Product added to cart');
    }

    public function removeFromCart($cartItemId)
    {
        Cart::destroy($cartItemId);
        // $this->notify('Product removed from cart');
    }

    public function clearCart(): ActionsAction
    {
        return ActionsAction::make('clearCart')
            // ->modalDescription('Are you sure you want to clear the cart? This action cannot be undone.')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(fn() => $this->confirmClearCart());
    }

    public function confirmClearCart()
    {
        try {
            Cart::where('session_id', $this->sessionID)->delete();
            $this->notify('Cart cleared successfully');
        } catch (\Exception $e) {
            $this->notify('Failed to clear cart', 'error');
        }
    }

    public function decreaseQty($cartItemId)
    {
        $cartItem = Cart::find($cartItemId);
        if ($cartItem->qty > 1) {
            $cartItem->qty -= 1;
            $cartItem->total = $cartItem->price * $cartItem->qty;
            $cartItem->save();
        } else {
            $this->removeFromCart($cartItemId);
        }
        // $this->notify('Quantity updated');
    }

    public function increaseQty($cartItemId)
    {
        $cartItem = Cart::find($cartItemId);
        $cartItem->qty += 1;
        $cartItem->total = $cartItem->price * $cartItem->qty;
        $cartItem->save();
        // $this->notify('Quantity updated');
    }

    #[Computed]
    public function cart()
    {
        return Cart::where('session_id', $this->sessionID)->get();
    }

    #[Computed]
    public function cartSubtotal()
    {
        return $this->cart->sum('total');
    }

    #[Computed]
    public function cartDiscount()
    {
        return $this->cart->sum('discount');
    }

    #[Computed]
    public function cartVat()
    {
        return $this->cart->sum('vat');
    }

    #[Computed]
    public function cartTotal()
    {
        return $this->cartSubtotal - $this->cartDiscount + $this->cartVat;
    }

    private function notify($message, $type = 'success')
    {
        Notification::make()
            ->title($message)
            ->status($type)
            ->send();
    }
}
