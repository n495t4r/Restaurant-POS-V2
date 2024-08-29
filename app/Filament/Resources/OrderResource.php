<?php

namespace App\Filament\Resources;

use App\Filament\Imports\OrderImporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderChannel;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Order';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     // return PostOrderResource::form($form);
    // }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    // Section::make('Details')
                    // ->icon('heroicon-m-information-circle')
                    // ->grow(false)
                    // ->schema([

                    // Section::make('Items')
                    // ->icon('heroicon-m-list-bullet')
                    //     ->schema([
                    //         TextEntry::make('packs')
                    //             ->label('')
                    //             ->html()
                    //             ->formatStateUsing(function ($record) {
                    //                 $packs = $record->packs;
                    //                 $output = '';
                    //                 $grandTotal = 0;
                    //                 $num = 1;
                    //                 foreach ($packs as $pack) {
                    //                     $output .= '<strong><i>Pack '.$num++.'</i></strong> ' . $pack->package_number . "\n";
                    //                     $subTotal = 0;
                    //                     foreach ($pack->items as $item) {
                    //                         $output .= '<strong>' . $item->quantity . 'x </strong>' . $item->product->name . ' | ' . $item->price . "\n";
                    //                         $subTotal += $item->price;
                    //                     }

                    //                     $output .= "<i>Sub-Total: ".$subTotal.'</i>';
                    //                     $output .= "\n\n";
                    //                     $grandTotal += $subTotal;
                    //                 }
                    //                 $output .= "<strong><i>Order Amount: ".$grandTotal.'</i></strong>';
                    //                 return nl2br($output);
                    //             }),
                    //     ]),

                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                        'lg' => 4,
                        'xl' => 5,
                        '2xl' => 5,
                    ])
                        ->schema([
                            Fieldset::make('Details')
                                // ->collapsed()
                                ->schema([
                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 3,
                                        'md' => 3,
                                        'lg' => 4,
                                        'xl' => 5,
                                        '2xl' => 5,
                                    ])
                                        ->schema([
                                            TextEntry::make('id')
                                                ->label('Order ID')
                                                ->copyable()
                                                ->copyMessage('Copied!'),
                                            TextEntry::make('status')
                                                ->label('Order status')
                                                ->badge()
                                                ->color(fn(string $state): string => match ($state) {
                                                    'null' => 'gray',
                                                    'pending' => 'warning',
                                                    'processed' => 'success',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                }),
                                            TextEntry::make('channel.channel'),
                                            TextEntry::make('payments.payment_method.name')
                                                ->default('N/A')
                                            // ->badge()
                                            ,
                                            TextEntry::make('created_at')
                                                ->label('Posted')
                                                ->since(),
                                        ]),
                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 2,
                                        'md' => 2,
                                        'lg' => 2,
                                        'xl' => 2,
                                        '2xl' => 2,
                                    ])
                                        ->schema([
                                            TextEntry::make('items')
                                                // ->label('')
                                                ->html()
                                                ->formatStateUsing(function ($record) {
                                                    $items = $record->items;
                                                    $output = '';
                                                    $subTotal = 0;

                                                    foreach ($items as $item) {
                                                        $output .= '<strong>' . $item->quantity . 'x </strong>' . $item->product->name . ' | ' . number_format($item->price, 2) . "\n";
                                                        $subTotal += $item->price;
                                                    }

                                                    $output .= "<i>Sub-Total: " . number_format($subTotal, 2) . '</i>';
                                                    $output .= "\n\n";

                                                    return nl2br($output);
                                                }),

                                            TextEntry::make('commentForCook')
                                                ->label('Note for cook')
                                                ->default('none')
                                        ])
                                ]),
                            Fieldset::make('More details')
                                // ->collapsed()
                                ->schema([
                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 2,
                                        'md' => 3,
                                        'lg' => 4,
                                        'xl' => 5,
                                        '2xl' => 5,
                                    ])
                                        ->schema([
                                            TextEntry::make('created_at')
                                                ->label('Date')
                                                ->dateTime(),
                                            TextEntry::make('customer.name'),

                                            // TextEntry::make('packs.items_sum_price')->sum('packs.items','price')
                                            // ->label('Amount'),
                                            // TextEntry::make('payment_method.name'),
                                            TextEntry::make('reason')
                                                ->label('Cancellation note'),
                                            TextEntry::make('user')
                                                ->formatStateUsing(function (Order $record) {
                                                    $user = $record->user; // Access the related user
                                                    return $user->first_name . ' ' . $user->last_name;
                                                })
                                                ->label('Order posted by'),
                                            // ...
                                        ])
                                ])
                        ]),
                ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {

        // $customers = Customer::pluck('first_name', 'id')->toArray(); //change to customer.name instead
        // $customers = Customer::pluck('name', 'id')->toArray(); //change to customer.name instead
        // $channels = OrderChannel::pluck('channel', 'id')->toArray(); //change to customer.name instead
        // $payment_methods = PaymentMethod::pluck('name', 'id')->toArray(); //change to customer.name instead


        return $table
            // ->groupsOnly()
            // ->defaultGroup('created_at')

            ->paginated([10, 25, 50, 100, 200])
            ->groups([
                'channel.channel',
                // 'payments.payment_method.name',
                Group::make('created_at')
                    ->collapsible()
                    ->label('Daily Summary')
                    ->date()
                    ->orderQueryUsing(fn(Builder $query, string $direction) => $query->orderBy('created_at', $direction))
                // ->sortable(descending)
            ])->groupRecordsTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Group records'),
            )->groupingSettingsInDropdownOnDesktop()
            // ->poll('30s')
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    // ->numeric()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied')
                    ->tooltip('click once to copy order ID')
                    ->copyMessageDuration(1500)
                    ->sortable(),
                // Panel::make([
                // Stack::make([               
                Tables\Columns\TextColumn::make('Orders')
                    ->label('Order items')
                    // ->state(fn (Order $record): string => $record->items[0]->quantity)
                    ->state(function (Model $record): array {
                        $itemNames = [];

                        // Loop through each item associated with the record
                        foreach ($record->items as $item) {
                            // Get the product name for the current item
                            $productName = $item->product->name;

                            // Get the quantity for the current item
                            $itemQuantity = $item->quantity;

                            // Concatenate the product name and quantity
                            $itemNames[] = "$itemQuantity" . "x " . "$productName";
                        }

                        // Join the array of item names into a single string separated by commas
                        // return implode(', ', $itemNames);
                        return $itemNames;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->listWithLineBreaks(),
                // ]),

                // ])->collapsible(),
                Tables\Columns\SelectColumn::make('customer_id')
                    // ->searchable()
                    ->disabled(fn ($record) => $record->created_at->format('Y-m-d') != today()->format('Y-m-d') && auth()->id() != 2)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label('Customer name')
                    ->options(function (): array {
                        return Customer::all()->pluck('name', 'id')->all();
                    }),
                Tables\Columns\SelectColumn::make('channel_id')
                    // ->searchable()
                    // ->disabled(function($record){
                    //     if($record->channel_id === 6){
                    //         return true;
                    //     }
                    //     return false;
                    // })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->disabled(fn ($record) => $record->created_at->format('Y-m-d') != today()->format('Y-m-d') && auth()->id() != 2)
                    ->label('Order channel')
                    ->options(function (): array {
                        return OrderChannel::all()->pluck('channel', 'id')->all();
                    }),
                Tables\Columns\TextColumn::make('items_sum_price')
                    ->sum('items', 'price')
                    ->money('NGN')
                    // ->searchable()
                    ->label('Price')
                    ->summarize([
                        Sum::make()->money('NGN')->label('Total'),
                        // Range::make()
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum_paid')
                    ->money('NGN')
                    ->sum('payments', 'paid')
                    // ->searchable()
                    ->label('Paid')
                    ->default(0)
                    ->summarize([
                        Sum::make()->money('NGN')->label('Total'),
                        // Range::make()
                    ])
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments.payment_method.name')
                    // ->searchable()
                    ->wrap()
                    ->words(3)
                    ->size(TextColumnSize::ExtraSmall)
                    ->placeholder('unselected')
                    // ->relationship('payments', 'payment_method_id')
                    ->label('Payment method')
                    ->toggleable(isToggledHiddenByDefault: false)
                // ->multiple()
                ,
                Tables\Columns\TextColumn::make('order')
                    ->label('Payment status')
                    ->badge()
                    ->default('unknown')
                    ->formatStateUsing(function ($record) {
                        $sum_paid = Payment::where('order_id', $record->id)->sum('paid');
                        $sum_price = OrderItem::where('order_id', $record->id)->sum('price');

                        $paymentDifference = $sum_price - $sum_paid;

                        if ($paymentDifference <= 0) {
                            return 'paid';
                        } else if ($paymentDifference < $sum_price) {
                            return 'partial';
                        } else {
                            return 'unpaid';
                        }
                        // return $paymentDifference;
                    })->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn(string $state): string => match ($state) {
                        'unknown' => 'gray',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        default => 'secondary'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->label('Order status')
                    ->formatStateUsing(function ($state) {
                        if ($state == 1) {
                            return 'completed';
                        } else if ($state == 2) {
                            return 'pending';
                        } else if ($state == 0) {
                            return 'failed';
                        }
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'null' => 'gray',
                        '2' => 'warning',
                        '1' => 'success',
                        '0' => 'danger',
                        default => 'secondary'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('commentForCook')
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Notes')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Created by')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->searchPlaceholder('search order ID')
            ->defaultSort('id', 'desc')
            ->filtersFormWidth(MaxWidth::Small)
            ->filtersFormColumns(2)
            ->filters([
                Filter::make('POS')
                    ->label('ATM Card/POS')
                    ->query(fn(Builder $query) => $query->WhereHas('payments', function ($query) {
                        $query->where('payment_method_id', '=', 3);
                    })),
                Filter::make('Cash')
                    ->query(fn(Builder $query) => $query->WhereHas('payments', function ($query) {
                        $query->where('payment_method_id', '=', 1);
                    })),
                Filter::make('Transfer')
                    ->query(fn(Builder $query) => $query->WhereHas('payments', function ($query) {
                        $query->where('payment_method_id', '=', 2);
                    })),

                // SelectFilter::make('payments')
                //     ->multiple()
                //     ->form([
                //         Select::make('pay_method')
                //         ->multiple()
                //         ->options(function (): array {
                //             return PaymentMethod::all()->pluck('name', 'id')->all();
                //         }),
                //     ])
                //     ->indicateUsing(function (array $data): array {
                //         $indicators = [];
                //         // dd($data['pay_method']);
                //         if ($data['pay_method'] == 1 ?? null) {
                //             // dd($data['pay_method']);

                //             $indicators[] = Indicator::make('Cash')
                //                 ->removeField('Cash');
                //         }

                //         if ($data['pay_method'] == 2 ?? null) {
                //             $indicators[] = Indicator::make('Transfer')
                //                 ->removeField('Transfer');
                //         }

                //         if ($data['pay_method'] == 3 ?? null) {
                //             $indicators[] = Indicator::make('ATM/POS')
                //                 ->removeField('ATM/POS');
                //         }

                //         return $indicators;
                //     })
                //     ->query(function (Builder $query, array $data): Builder {

                //         // dd($data['pay_method']);

                //         return $query
                //             ->when(
                //                 $data['pay_method'] == 1,
                //                 // dd($data['pay_method']),

                //                 fn (Builder $query): Builder => $query->whereIn('id', Order::partial_payment()),
                //             )
                //             ->when(
                //                 $data['pay_method'] == 2,
                //                 fn (Builder $query): Builder => $query->whereIn('id', Order::no_payment()),
                //             )->when(
                //                 $data['pay_method'] == 3,
                //                 fn (Builder $query): Builder => $query->whereIn('id', Order::full_payment()),
                //             );
                //     }),
                SelectFilter::make('id')
                    ->multiple()
                    ->form([
                        Select::make('pay_status')
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                            ]),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['pay_status'] == 'partial' ?? null) {
                            $indicators[] = Indicator::make('Partial payment')
                                ->removeField('partial');
                        }

                        if ($data['pay_status'] == 'unpaid' ?? null) {
                            $indicators[] = Indicator::make('No payment')
                                ->removeField('unpaid');
                        }

                        if ($data['pay_status'] == 'paid' ?? null) {
                            $indicators[] = Indicator::make('Full payment')
                                ->removeField('paid');
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pay_status'] == 'partial',
                                fn(Builder $query): Builder => $query->whereIn('id', Order::partial_payment()),
                            )
                            ->when(
                                $data['pay_status'] == 'unpaid',
                                fn(Builder $query): Builder => $query->whereIn('id', Order::no_payment()),
                            )->when(
                                $data['pay_status'] == 'paid',
                                fn(Builder $query): Builder => $query->whereIn('id', Order::full_payment()),
                            );
                    }),

                SelectFilter::make('status')
                    ->label('Order status')
                    ->multiple()
                    ->options([
                        '2' => 'Pending',
                        '0' => 'Cancelled',
                        '1' => 'Completed',
                    ]),

                SelectFilter::make('customer_id')
                    ->multiple()
                    ->label('Customer')
                    ->options(function (): array {
                        return Customer::all()->pluck('name', 'id')->all();
                    }),

                SelectFilter::make('channel_id')
                    ->multiple()
                    ->label('Channel')
                    ->multiple()
                    ->options(function (): array {
                        return OrderChannel::all()->pluck('channel', 'id')->all();
                    }),

                // SelectFilter::make('items.product.product_category.name')
                //     ->label('Category')
                //     ->multiple()
                //     ->options(function (): array {
                //         return ProductCategory::all()->pluck('name', 'id')->all();
                //     }),
                SelectFilter::make('user_id')
                    ->label('Created by')
                    ->multiple()
                    ->options(function (): array {
                        return User::all()->pluck('first_name', 'id')->all();
                    }),
                Filter::make('created_at')
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
                
                Tables\Actions\Action::make('download')
                ->label('Print')
                ->icon('heroicon-m-printer')
                ->iconButton()
                ->url(
                    fn (Order $record): string => route('print.invoice', ['id' => $record->id]),
                    shouldOpenInNewTab: true
                ),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    \ArielMejiaDev\FilamentPrintable\Actions\PrintBulkAction::make(),
                ]),
            ]);
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
            // 'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),

            'index' => Pages\ManageOrders::route('/'),
        ];
    }
}
