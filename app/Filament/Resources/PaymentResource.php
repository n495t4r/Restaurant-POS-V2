<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $customers = Customer::pluck('name', 'id')->toArray(); //change to customer.name instead

        return $form
            ->schema([
                Split::make([
                    Grid::make(1)
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Select::make('order_id')
                                // ->relationship('order', 'id')
                                ->options(
                                    function () {

                                        return Order::whereNotIn('id', Order::full_payment())
                                        ->whereNot('status', 'failed')
                                        ->orderBy('id', 'desc')
                                        ->pluck('id');
                                        
                                    }
                                )
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    // dd($state);
                                    $query = Order::query()
                                        ->where('id', $state)
                                        ->pluck('channel_id', 'customer_id')->toArray();

                                    $sum_price = OrderItem::where('order_id', $state)->sum('price');
                                    $sum_paid = Payment::where('order_id', $state)->sum('paid');

                                    $set('customer', array_keys($query));
                                    $set('channel', array_values($query));
                                    $set('order.price', $sum_price);
                                    $set('total_paid', number_format($sum_paid, 2, '.', ''));
                                })->disabled(fn (string $operation) => $operation == 'edit')
                                ->required(),

                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->id())
                                ->required(),
                            Forms\Components\Select::make('payment_method_id')
                                ->relationship('payment_method', 'name')
                                ->required(),
                            Forms\Components\TextInput::make('paid')
                                ->label('Pay(balance)')
                                ->required()
                                ->placeholder(fn (Get $get): float => $get('order.price') - $get('total_paid'))
                                ->maxValue(fn (Get $get): float => $get('order.price') - $get('total_paid'))
                                ->minValue(50)
                                ->numeric()
                        ]),
                    Grid::make(1)
                        ->columnSpanFull()

                        ->grow(false)
                        ->schema([
                            Forms\Components\Select::make('customer')
                                ->relationship('order.customer', 'name')
                                ->formatStateUsing(function ($record, string $operation) {
                                    if ($operation === 'edit') {
                                        $order_id = $record->order_id;
                                        $state = Order::query()->where('id', $order_id)->pluck('customer_id');
                                        return $state;
                                    }
                                })
                                ->label('Customer')
                                ->disabled()
                                ->dehydrated(false)
                                // ->disabled(fn( ?string $state) => !empty($state))
                                ,
                            Forms\Components\Select::make('channel')
                                ->relationship('order.channel', 'channel')
                                ->formatStateUsing(function ($record, string $operation) {
                                    if ($operation === 'edit') {
                                        $order_id = $record->order_id;
                                        $state = Order::query()->where('id', $order_id)->pluck('channel_id');
                                        return $state;
                                    }
                                })
                                ->label('Channel')
                                ->dehydrated(false)
                                ->disabled(),
                            Forms\Components\TextInput::make('order.price')
                                ->disabled()
                                ->label('Order Amount')
                                ->formatStateUsing(function ($record, string $operation) {
                                    if ($operation === 'edit') {
                                        $sum_price = OrderItem::where('order_id', $record->order_id)->sum('price');
                                        return $sum_price;
                                    }
                                }),

                            Forms\Components\TextInput::make('total_paid')
                                ->formatStateUsing(function ($record, string $operation) {
                                    if ($operation === 'edit') {
                                        $sum_price = $record->paid;
                                        return $sum_price;
                                    }
                                })
                                ->label('Total paid')
                                ->disabled(),
                        ])
                ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Created by')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
