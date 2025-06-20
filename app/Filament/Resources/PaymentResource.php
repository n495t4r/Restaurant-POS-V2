<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        $customers = Customer::pluck('name', 'id')->toArray(); //change to customer.name instead
        $unpaid_orders = Order::whereNotIn('id', Order::full_payment())
            ->whereNot('status', 0)->get();

        return $form
            ->columns(1)
            ->schema(static::getFormSchema());
    }

    public static function getFormSchema2(): array
    {
        return [
            Split::make([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Unpaid orders')
                            ->searchable()
                            ->columnSpan(2)

                            ->preload()
                            // ->relationship('order', 'id')
                            ->options(
                                function () {

                                    $orders = Order::whereNotIn('id', Order::full_payment())
                                        ->whereNotIn('id', Order::failed_order())
                                        ->whereNotIn('id', Order::staff_order())
                                        ->whereNotIn('id', Order::glovo_order())
                                        ->whereNotIn('id', Order::chowdeck_order())
                                        ->orderBy('id', 'desc')
                                        ->get();
                                    $f_string = [];

                                    foreach ($orders as $order) {
                                        $customerName = $order->customer ? $order->customer->name : 'Unselected';
                                        $timeAgo = Carbon::parse($order->created_at)->diffForHumans();

                                        $f_string[$order->id] = $order->id . ' (' . $customerName . ') ' . $timeAgo;
                                    }

                                    return $f_string;
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
                            })->disabled(fn(string $operation) => $operation == 'edit')
                            ->required(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id())
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('paid')
                    ->label('Pay(balance)')
                    ->columnSpanFull()
                    ->required()
                    ->placeholder(fn(Get $get): float => $get('order.price') - $get('total_paid'))
                    ->maxValue(fn(Get $get): float => $get('order.price') - $get('total_paid'))
                    ->minValue(50)
                    ->step(50)
                    ->numeric(),
                Forms\Components\Select::make('payment_method_id')
                    ->relationship('payment_method', 'name')
                    ->columnSpanFull()
                    ->options(function (): array {
                        return PaymentMethod::query()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($paymentmethod) {
                                $label = $paymentmethod->name;
                                if (!$paymentmethod->is_active) {
                                    $label .= ' (inactive)';
                                }
                                return [$paymentmethod->id => $label];
                            })
                            ->toArray();
                    })
                    ->disableOptionWhen(function (string $value, string $label): bool {
                        $paymentmethod = PaymentMethod::find($value);
                        return $paymentmethod && !$paymentmethod->is_active;
                    })
                    ->required(),


                Grid::make()
                    // ->columnSpanFull()

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
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Split::make([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Unpaid orders')
                            ->searchable()
                            ->columnSpan(2)
                            ->preload()
                            ->options(function () {
                                $orders = Order::whereNotIn('id', Order::full_payment())
                                    ->whereNotIn('id', Order::failed_order())
                                    ->whereNotIn('id', Order::staff_order())
                                    ->whereNotIn('id', Order::glovo_order())
                                    ->whereNotIn('id', Order::chowdeck_order())
                                    ->orderBy('id', 'desc')
                                    ->get();
                                $f_string = [];

                                foreach ($orders as $order) {
                                    $customerName = $order->customer_id ? Customer::find($order->customer_id)->name : 'Unselected';
                                    $timeAgo = Carbon::parse($order->created_at)->diffForHumans();

                                    $f_string[$order->id] = $order->id . ' (' . $customerName . ') ' . $timeAgo;
                                }

                                return $f_string;
                            })
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $order = Order::find($state);
                                    $sum_price = OrderItem::where('order_id', $state)->sum('price');
                                    $sum_paid = Payment::where('order_id', $state)->sum('paid');

                                    $set('customer', $order->customer_id);
                                    $set('channel', $order->channel_id);
                                    $set('order.price', $sum_price);
                                    $set('total_paid', number_format($sum_paid, 2, '.', ''));
                                }
                            })
                            ->disabled(fn(string $operation) => $operation === 'edit')
                            ->required(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id())
                            ->required(),

                        Forms\Components\Select::make('payment_method_id')
                            ->label('Payment Method')
                            ->columnSpan([
                                'default' => 2,
                                'sm' => 2,
                                'md' => 1,
                                'lg' => 1,
                                'xl' => 1,
                                '2xl' => 1])
                            ->options(function (): array {
                                return PaymentMethod::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($paymentmethod) {
                                        $label = $paymentmethod->name;
                                        if (!$paymentmethod->is_active) {
                                            $label .= ' (inactive)';
                                        }
                                        return [$paymentmethod->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->disableOptionWhen(function (string $value, string $label): bool {
                                $paymentmethod = PaymentMethod::find($value);
                                return $paymentmethod && !$paymentmethod->is_active;
                            })
                            ->required(),

                        Forms\Components\TextInput::make('paid')
                        ->columnSpan([
                            'default' => 2,
                            'sm' => 2,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 1])
                            ->label('Pay(balance)')
                            ->required()
                            ->placeholder(fn(Forms\Get $get): float => $get('order.price') - $get('total_paid'))
                            ->maxValue(fn(Forms\Get $get): float => $get('order.price') - $get('total_paid'))
                            ->minValue(50)
                            ->step(50)
                            ->numeric(),
                    ]),

                Grid::make()
                    ->grow(false)
                    ->schema([
                        Forms\Components\Select::make('customer')
                            ->label('Customer')
                            ->options(function () {
                                return Customer::pluck('name', 'id');
                            })
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('channel')
                            ->label('Channel')
                            ->options(function () {
                                return OrderChannel::pluck('channel', 'id');
                            })
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('order.price')
                            ->disabled()
                            ->label('Order Amount'),

                        Forms\Components\TextInput::make('total_paid')
                            ->label('Total paid')
                            ->disabled(),
                    ])
            ])
        ];
    }

    public static function makePaymentAction(): Action
    {
        return Action::make('makePayment')
            ->label('Make Payment')
            ->form(static::getFormSchema())
            ->action(function (array $data) {
                Payment::create($data);
            })
            ->modalHeading('Make Payment')
            ->modalSubmitActionLabel('Pay');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Payment ID')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Created by')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.created_at')
                    ->label('Order date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Payment date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([

                Filter::make('created_at')
                    ->label('Payment Date')
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('From ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    })
                    ->form([
                        DatePicker::make('from')
                            ->default(now()),
                        DatePicker::make('until')->afterOrEqual('from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
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
