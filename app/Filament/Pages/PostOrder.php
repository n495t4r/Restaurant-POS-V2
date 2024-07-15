<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Summarizers\Sum;

class PostOrder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.post-order';

    public ?array $data = []; 

    public function mount(): void 
    {
        $this->form->fill();
    }
 
    public function form(Form $form): Form
    {
        // $products = Product::all()->where('status','!=',0); //change to customer.name instead
        
        $products = Product::get();

        $customers = Customer::pluck('first_name', 'id')->toArray(); //change to customer.name instead

        $options = [];

        // Assuming $products is an array of Product models or arrays with 'name' and 'qty' attributes
        // foreach ($products as $product) {
        //     // Assuming $product is an instance of a Product model
        //     $options[$product->name] = $product->name . ' (' . $product->quantity . ')';
        // }
        
        return $form
            ->columns(1)
            ->schema([
        // Split::make([
            Section::make('Operations')
            ->columns(3)
            ->columnSpan(1)
            ->schema([
                CheckboxList::make('payment_method')
                    ->options([
                        'POS' => 'POS',
                        'Transfer' => 'Transfer',
                        'Cash' => 'Cash',
                    ]),
                Select::make('Customer')
                    ->options($customers)
                    ->preload()
                    ->searchable(),
                TextInput::make('notforcook')
                    ->label('Note for cook')
                    ->placeholder('Enter note for cook'),
            ]),

            // Section::make()
            // ->columns(1)
            // ->maxWidth('1/2')
            // ->schema([
            //     TextInput::make('gtotal')
            //         // ->label('Grand Total')
            //         ->numeric()
            //         // Read-only, because it's calculated
            //         ->readOnly()
            //         ->default(0)
            //         ->prefix('NGN')
            //     ]),

            
        Fieldset::make('Cart')
            ->columns(1)
            ->schema([ 
            Builder::make('Cart')
            ->label('')
            ->reorderableWithButtons()
            ->reorderableWithDragAndDrop(false)
            ->cloneable()
            ->collapsed(true)
            ->collapsible()
            ->blocks([
                Block::make('Pack')
                ->schema([
                    Grid::make(4)
                    ->schema([
                        TextInput::make('Product Name')
                        ->columnSpan(2)
                        
                        // Read-only, because it's calculated
                        ->readOnly(),
                        TextInput::make('Quantity')
                        ->columnSpan(1)
                        
                        // Read-only, because it's calculated
                        ->readOnly(),
                        TextInput::make('Price')
                        ->columnSpan(1)
                        // Read-only, because it's calculated
                        ->readOnly()
                    ]),

                    Repeater::make('Order')
                    ->hiddenLabel()
                    ->columns(4)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->options(
                                $products->mapWithKeys(function (Product $product) {
                                    return [$product->id => sprintf('%s | N%s | %s', $product->name, $product->price, $product->quantity)];
                                })
                            )
                            ->disableOptionWhen(function ($value, $state, Get $get) {
                                return collect($get('../*.product_id'))
                                    ->reject(fn($id) => $id == $state)
                                    ->filter()
                                    ->contains($value);
                            })
                            ->preload()
                            ->searchable()
                            // ->getSearchResultsUsing(fn (string $search): array => Product::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray())
                            ->placeholder('Select a product')
                            ->label('')
                            // ->label('Products')
                            ->autofocus()
                            ->loadingMessage('Loading products...')
                            ->noSearchResultsMessage('product not found.')
                            ->optionsLimit(5)
                            ->columnSpan(2)
                            // ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) { 
                                self::updatePrice($get,$set);
                            })
                            ->required(),
                        TextInput::make('quantity')
                        ->numeric()
                        ->default(1)
                        ->label('')
                        ->minValue(1)
                        ->maxValue(500)
                        // ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) { 
                            self::updatePrice($get,$set);
                        })
                        // ->columnSpan(1/4)
                        ->step(1),
                        TextInput::make('Price')
                            ->disabled()
                            ->label('')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->columnSpan(1)
                            ->default(0)  
                            ->dehydrated(false),
                        Hidden::make('price')
                        ->disabled()
                    ])->live(true)
                    ->afterStateUpdated(function (Get $get, Set $set) { 
                        self::updateSTotals($get,$set);
                        self::updateGrandTotal($get,$set);
                    }),
                    Grid::make(3)
                    ->schema([
                        TextInput::make('subtotal')
                        ->columnSpan(1)
                        // Read-only, because it's calculated
                        ->readOnly()
                        ->prefix('NGN')
                    ]),
                ])
            ])->addActionLabel('Add pack'),
                
            Grid::make(3)
                ->schema([
                    TextInput::make('gtotal')
                    ->columnSpan(1)
                    // Read-only, because it's calculated
                    ->readOnly()
                    ->prefix('NGN')
                ]),
            
        ])
        
        // ])

            ])
            ->statePath('data');
    } 

    public static function updatePrice(Get $get, Set $set){
        $price = Product::where('id', '=', $get('product_id'))->value('price');
        // $set('Price', $price);
        $set('Price', number_format($get('quantity')*$price, 2, '.', '',));
        // self::updateTotals($get, $set);
    }

    public static function updateSTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('Order'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
    
        // // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);
    
        // Update the state with the new values
        $set('subtotal', number_format($subtotal, 2, '.', ''));
        
    }

    public static function updateGrandTotal(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('Order'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
    
        // // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);
    
        // Update the state with the new values
        $set('gtotal', 5000+200);        
    }
    
    // This function updates totals based on the selected products and quantities
    public static function updateTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('Order'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
    
        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
    
        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);
    
        // Update the state with the new values
        $set('price', number_format($subtotal, 2, '.', ''));
        // $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100)), 2, '.', ''));
    }
}
