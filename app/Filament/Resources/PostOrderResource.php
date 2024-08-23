<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostOrderResource\Pages;
use App\Filament\Resources\PostOrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\PaymentMethod;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PostOrderResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'Cart';

    // protected static ?string $navigationGroup = 'Order';
    protected static ?int $navigationSort = 0;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        // $products = Product::all()->where('status','!=',0); //change to customer.name instead

        $products = Product::where('status', true)->get();

        $products2 = [];
        foreach ($products as $product) {
            $formattedString = $product->name . ' | N' . $product->price . ' | ' . $product->quantity;
            $products2[$product->id] = $formattedString;
        }
        $customers = Customer::where('is_active', true)->pluck('name', 'id')->toArray(); //change to customer.name instead
        $payment_methods = PaymentMethod::where('is_active', true)->pluck('name', 'id')->toArray(); //change to customer.name instead
        $channels = OrderChannel::where('is_active', true)->pluck('channel', 'id')->toArray(); //change to customer.name instead

        $options = [];
        // $packName = 'Pack_' . time(); // Append current timestamp to pack name


        // Assuming $products is an array of Product models or arrays with 'name' and 'qty' attributes
        // foreach ($products as $product) {
        //     // Assuming $product is an instance of a Product model
        //     $options[$product->name] = $product->name . ' (' . $product->quantity . ')';
        // }

        return $form
            ->columns(1)
            ->schema([
                // Actions::make([
                //     Action::make('star')
                //         ->icon('heroicon-m-star')
                //         // ->requiresConfirmation()
                //         ->form([
                //             Select::make('customer_id')
                //                 ->label('Customer')
                //                 ->options(Customer::pluck('name', 'id')->toArray())
                //                 ->preload()
                //                 ->searchable()
                //                 // ->getSearchResultsUsing(fn (string $search): array => Product::where('name', 'like', "%{$search}%")->limit(6)->pluck('name', 'id')->toArray())
                //                 ->placeholder('Assign order to customer')
                //                 ->autofocus()
                //                 ->loadingMessage('Loading customer...')
                //                 ->noSearchResultsMessage('customer not found.')
                //                 ->optionsLimit(5)
                //                 ->columnStart(1)
                //         ])
                //     // ->action(function (array $data, Post $record): void {
                //     //     $record->author()->associate($data['authorId']);
                //     //     $record->save();
                //     // })
                //     ,
                //     Action::make('resetStars')
                //         ->icon('heroicon-m-x-mark')
                //         ->color('danger')
                //         ->requiresConfirmation()
                //     // ->action(function (ResetStars $resetStars) {
                //     //     $resetStars();
                //     // })
                //     ,
                // ])->fullWidth(),

                Section::make('Operations')
                    ->columns(3)
                    ->columnSpan(1)
                    ->schema([


                        // CheckboxList::make('payment_methods')
                        //     ->relationship('payments', 'payment_methods')
                        //     ->options([
                        //         'POS' => 'POS',
                        //         'Transfer' => 'Transfer',
                        //         'Cash' => 'Cash',
                        //     ]),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options($customers)
                            ->preload()
                            // ->live()
                            // ->afterStateUpdated(fn (Set $set, ?string $state) => $set('payments.order_id', $state))
                            ->searchable(),
                        Select::make('payment_method_id')
                            ->live()
                            //     ->afterStateUpdated(fn (Set $set, ?string $state) => $set('payments.order_id', $state))
                            ->label('Payment Method')
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('payments.payment_method_id', $state))
                            ->options($payment_methods)
                            ->preload(),
                        // TextInput::make('customer_idd'),

                        Select::make('channel_id')
                            ->label('Order Channel')
                            ->options($channels)
                            ->preload(),
                        TextInput::make('commentForCook')
                            ->label('Note for cook')
                            ->placeholder('Enter note for cook'),
                        // TextInput::make('amount')
                        //     ->label('Order Amount')
                        //     ->mask(RawJs::make('$money($input)'))
                        //     ->stripCharacters(',')
                        //     ->numeric()
                        //     ->readOnly()
                        //     ->live()
                        //     // ->columnSpan(1)
                        //     ->columnStart(3)
                        //     ->default(0),
                        TextInput::make('paid')
                            ->numeric()
                            // ->mask(RawJs::make('$money($input)'))
                            // ->stripCharacters(',')
                            // ->default(0)
                            ->step(50)
                            ->formatStateUsing(function ($record, string $operation) {
                                if ($operation === 'edit') {
                                    return 200;
                                }
                            })
                    ]),


                Fieldset::make('Cart')
                    ->columns(1)
                    ->schema([
                        Repeater::make('packs')
                            // ->relationship()
                            ->reorderableWithButtons()
                            ->reorderableWithDragAndDrop(false)
                            ->cloneable()
                            ->collapsed(true)
                            ->collapsible()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('Product Name')
                                            ->columnStart(1)
                                            ->dehydrated(false)
                                            // Read-only, its a heading
                                            ->readOnly(),
                                        TextInput::make('Quantities')
                                            ->columnStart(2)
                                            // ->columnSpan(1)
                                            ->dehydrated(false)
                                            // Read-only, because it's a table heading
                                            ->readOnly(),
                                        TextInput::make('Prices')
                                            // ->columnSpan(1)
                                            ->columnStart(3)
                                            ->dehydrated(false)
                                            // Read-only, because it's a heading
                                            ->readOnly()
                                    ])->columnSpan('full'),

                                Repeater::make('items')
                                    // ->relationship()
                                    ->columns(4)
                                    ->columnSpan('full')
                                    ->schema([
                                        Select::make('product_id')
                                            // ->relationship('product', 'name')
                                            ->options(
                                                // $products->mapWithKeys(function (Product $product) {
                                                //     return [$product->id => sprintf('%s | N%s | %s', $product->name, $product->price, $product->quantity)];
                                                // })
                                                $products2
                                            )
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->preload()
                                            ->searchable()
                                            // ->getSearchResultsUsing(fn (string $search): array => Product::where('name', 'like', "%{$search}%")->limit(6)->pluck('name', 'id')->toArray())
                                            ->placeholder('Select a product')
                                            ->label('')
                                            // ->label('Products')
                                            ->autofocus()
                                            ->loadingMessage('Loading products...')
                                            ->noSearchResultsMessage('product not found.')
                                            ->optionsLimit(5)
                                            ->columnStart(1)
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                // dd($state);

                                                $qty = Product::where('id', $state)->pluck('quantity')->first();
                                                if (
                                                    $qty <= 0
                                                ) {

                                                    Action::make('delete')
                                                        ->action(fn ($record) => $record->delete())
                                                        ->requiresConfirmation()
                                                        ->modalHeading('Delete post')
                                                        ->modalDescription('Are you sure you\'d like to delete this post? This cannot be undone.')
                                                        ->modalSubmitActionLabel('Yes, delete it');
                                                }

                                                self::updatePrice($get, $set);
                                            })
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->label(false)
                                            ->minValue(1)
                                            ->columnStart(2)
                                            ->maxValue(500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::updatePrice($get, $set);
                                            })
                                            ->step(1),
                                        TextInput::make('price')
                                            ->readOnly()
                                            ->label(false)
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            // ->reactive()
                                            ->live()
                                            // ->columnSpan(1)
                                            ->columnStart(3)
                                            ->default(0),
                                        // Hidden::make('price')
                                    ])
                                    ->live(true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateSTotals($get, $set);
                                        self::updateGrandTotal($get, $set);
                                    }),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->columnSpan(1)
                                            ->dehydrated(false)
                                            // Read-only, because it's calculated
                                            ->readOnly()
                                            ->live()
                                            ->prefix('NGN')

                                    ]),
                            ])->addActionLabel('Add Pack')
                        // ,
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('gtotal')
                            ->columnSpan(1)
                            ->dehydrated(false)
                            // Read-only, because it's calculated
                            ->readOnly()
                            ->prefix('NGN')
                    ]),

            ]);
    }

    public static function updatePrice(Get $get, Set $set)
    {
        $price = Product::where('id', '=', $get('product_id'))->value('price');
        // $set('Price', $price);
        // $set('product_price', number_format($get('quantity')*$price, 2, '.', '',));
        $set('price', number_format($get('quantity') * $price, 2, '.', '',));
        // self::updateTotals($get, $set);
    }

    public static function updateSTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('items'))->filter(fn ($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
        $set('subtotal', number_format($subtotal, 2, '.', ''));
        // $set('../../../Operations.amount', number_format($subtotal, 2, '.', ''));
    }

    public static function updateGrandTotal(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('items'))->filter(fn ($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
        $set('gtotal', 5000 + 200);
    }

    // This function updates totals based on the selected products and quantities
    public static function updateTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('items'))->filter(fn ($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        // Update the state with the new values
        // $set('price', number_format($subtotal, 2, '.', ''));
        // $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100)), 2, '.', ''));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ListOrders::route('/'),
            'index' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
