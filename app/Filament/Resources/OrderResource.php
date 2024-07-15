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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Order';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return PostOrderResource::form($form);
    }

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
                                                ->color(fn (string $state): string => match ($state) {
                                                    'null' => 'gray',
                                                    'pending' => 'warning',
                                                    'processed' => 'success',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                }),
                                            TextEntry::make('channel.channel'),
                                            TextEntry::make('payments.status')
                                                ->default('N/A')
                                                ->badge()
                                                ->color(fn (string $state): string => match ($state) {
                                                    'null' => 'gray',
                                                    'partial' => 'warning',
                                                    'paid' => 'success',
                                                    'unpaid' => 'danger',
                                                    default => 'secondary'
                                                }),
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
                                                        $output .= '<strong>' . $item->quantity . 'x </strong>' . $item->product->name . ' | ' . $item->price . "\n";
                                                        $subTotal += $item->price;
                                                    }

                                                    $output .= "<i>Sub-Total: " . $subTotal . '</i>';
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
            // ->defaultGroup('id')
            // ->paginated([10, 25, 50, 100, 'all'])
            ->groups([
                'channel.channel',
                'pay_method.name',
                Group::make('created_at')
                    ->collapsible()
                    ->label('Daily Summary')
                    ->date()
            ])->groupRecordsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Group records'),
            )->groupingSettingsInDropdownOnDesktop()
            ->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    // ->numeric()
                    ->searchable()
                    ->copyMessage('ID copied')
                    ->copyMessageDuration(1500)
                    ->sortable(),
                Tables\Columns\SelectColumn::make('customer_id')
                    ->searchable()
                    ->label('Customer name')
                    ->options(function (): array {
                        return Customer::all()->pluck('name', 'id')->all();
                    }),
                Tables\Columns\SelectColumn::make('channel_id')
                    ->searchable()
                    // ->disabled()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->label('Order channel')
                    ->options(function (): array {
                        return OrderChannel::all()->pluck('channel', 'id')->all();
                    }),
                Tables\Columns\TextColumn::make('items_sum_price')
                    ->sum('items', 'price')
                    ->money('NGN')
                    ->searchable()
                    ->label('Amount')
                    ->summarize([
                        Sum::make()->money('NGN')->label('Total'),
                        // Range::make()
                    ])
                    ->sortable(),
                // Tables\Columns\TextColumn::make('payments.amount')
                //     ->money('NGN')
                //     ->searchable()
                //     ->label('Amount')
                //     ->summarize([
                //         Sum::make()->money('NGN')->label('Total'),
                //         // Range::make()
                //     ])
                //     ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum_paid')
                    ->money('NGN')
                    ->sum('payments', 'paid')
                    ->searchable()
                    ->label('Paid amount')
                    ->summarize([
                        Sum::make()->money('NGN')->label('Total'),
                        // Range::make()
                    ])
                    ->sortable(),
                Tables\Columns\SelectColumn::make('payment_method_id')
                    ->searchable()
                    // ->relationship('payments', 'payment_method_id')
                    ->label('Payment type')
                    // ->multiple()
                    ->options(function (): array {
                        return PaymentMethod::all()->pluck('name', 'id')->all();
                    }),
                Tables\Columns\TextColumn::make('order')
                    ->label('Payment')
                    ->badge()
                    ->default('unknown')
                    ->formatStateUsing(function ($record){
                        $sum_paid = Payment::where('order_id',$record->id)->sum('paid');
                        $sum_price = OrderItem::where('order_id',$record->id)->sum('price');
                        
                        // dd($items);

                        $paymentDifference = $sum_price - $sum_paid;

                        if ($paymentDifference <= 0) {
                            return 'paid';
                        } else if ($paymentDifference < $sum_price) {
                            return 'partial';
                        } else {
                            return 'unpaid';
                        }
                        // return $paymentDifference;
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unknown' => 'gray',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        default => 'secondary'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Order status')
                    ->color(fn (string $state): string => match ($state) {
                        'null' => 'gray',
                        'pending' => 'warning',
                        'processed' => 'success',
                        'failed' => 'danger',
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
            ->searchPlaceholder('ID, Amount, Customer etc')
            ->defaultSort('id', 'desc')
            ->filtersFormWidth(MaxWidth::Small)
            ->filtersFormColumns(2)
            ->filters([
                Filter::make('POS')
                    ->query(fn (Builder $query) => $query->orWhereHas('pay_method', function ($query) {
                        $query->where('name', 'like', '%POS%');
                    })),
                Filter::make('Cash')
                    ->query(fn (Builder $query) => $query->orWhereHas('pay_method', function ($query) {
                        $query->where('name', 'like', '%Cash%');
                    })),
                Filter::make('Transfer')
                    ->query(fn (Builder $query) => $query->orWhereHas('pay_method', function ($query) {
                        $query->where('name', 'like', '%Transfer%');
                    })),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'failed' => 'Cancelled',
                        'processed' => 'Completed',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(function (): array {
                        return Customer::all()->pluck('name', 'id')->all();
                    }),

                SelectFilter::make('channel_id')
                    ->label('Channel')
                    ->options(function (): array {
                        return OrderChannel::all()->pluck('channel', 'id')->all();
                    }),

                SelectFilter::make('items.product.product_category.name')
                    ->label('Category')
                    ->options(function (): array {
                        return ProductCategory::all()->pluck('name', 'id')->all();
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
