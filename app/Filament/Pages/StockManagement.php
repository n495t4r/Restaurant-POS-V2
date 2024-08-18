<?php
// app/Filament/Pages/StockManagement.php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockHistory;
use Filament\Pages\Page;
use Carbon\Carbon;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as HttpRequest;

class StockManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static string $view = 'filament.pages.stock-management';
    protected static ?string $title = 'EOD Report';
    // protected static ?string $navigationLabel = 'Stock Management';
    protected static ?string $slug = 'eod-report';

    public ?array $data = [];

    public $start_date;
    public $end_date;
    public $report;

    // public function mount()
    // {
    // //     // Default to previous day
    // //     $this->start_date = Carbon::yesterday()->toDateString();
    // //     $this->end_date = Carbon::today()->toDateString();

    //     Product::all()->toArray();

    // }

    public Order $order;





    public function reportsInfolist(Infolist $infolist): Infolist
    {

        $this->order = Order::get()->first();

        // dd($this->view);
        $product = Product::all()->toArray();
        // dd($this->prod);

        return $infolist
            ->record($this->order)
            ->schema([
                Split::make([

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
                                            TextEntry::make('cash_payment')
                                                ->default(0)
                                                ->badge()
                                                ->money('NGN')

                                                ->formatStateUsing(function () {
                                                    $sum_paid = Payment::sum_by_method(1);
                                                    return number_format($sum_paid, 2);
                                                }),

                                            TextEntry::make('Transfer_transaction')
                                                ->default(0)
                                                ->badge()
                                                ->formatStateUsing(function () {
                                                    $sum_paid = Payment::sum_by_method(2);
                                                    return number_format($sum_paid, 2);
                                                }),
                                            TextEntry::make('ATM/POS_payment')
                                                ->default(0)
                                                ->badge()
                                                ->formatStateUsing(function () {
                                                    $sum_paid = Payment::sum_by_method(3);
                                                    return number_format($sum_paid, 2);
                                                }),
                                            TextEntry::make('Staff_order')
                                                ->default(0)
                                                ->badge()
                                                ->formatStateUsing(function () {
                                                    $sum_paid = Payment::sum_by_method(4);
                                                    return number_format($sum_paid, 2);
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
                                                    // unpaidOrders();

                                                    return nl2br($this->unpaidOrders());
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
                ]),

                Section::make('Stock Reports')
                    ->columns(2)
                    ->compact()
                    ->collapsible()
                    ->aside()
                    ->description('View detailed reports about your stock levels, transactions, and more.')

                    ->schema([
                        Section::make('Sales N'.number_format(Payment::sum_by_method(0),2))
                            ->description('The items you have selected for purchase')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('cash_payment')
                                    ->default(0)
                                    ->badge()
                                    ->money('NGN')

                                    ->formatStateUsing(function () {
                                        $sum_paid = Payment::sum_by_method(1);
                                        return number_format($sum_paid, 2);
                                    }),

                                TextEntry::make('Transfer_transaction')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Payment::sum_by_method(2);
                                        return number_format($sum_paid, 2);
                                    }),
                                TextEntry::make('ATM/POS_payment')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Payment::sum_by_method(3);
                                        return number_format($sum_paid, 2);
                                    }),
                                TextEntry::make('Staff_order')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Payment::sum_by_method(4);
                                        return number_format($sum_paid, 2);
                                    }),
                            ])
                            ->collapsed(),

                        Section::make('Expenses made')
                            ->description('Prevent abuse by limiting the number of requests per period')
                            ->schema([
                                TextEntry::make('Total')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Expense::sum_by_method(0);
                                        return number_format($sum_paid, 2);
                                    }),
                                TextEntry::make('Cash')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Expense::sum_by_method(1);
                                        return number_format($sum_paid, 2);
                                    }),
                                TextEntry::make('Transfer')
                                    ->default(0)
                                    ->badge()
                                    ->formatStateUsing(function () {
                                        $sum_paid = Expense::sum_by_method(2);
                                        return number_format($sum_paid, 2);
                                    }),
                            ])
                            ->compact()
                    ]),

            ])->columns(1);
    }

    public function unpaidOrder2()
    {
        $unpaidOrders = Order::whereNotIn('id', Order::full_payment())
            ->where('status', '!=', 'failed')
            ->orderBy('id', 'desc')
            ->get();

        $output = " <div class='filament-resource-table'>
        <x-filament::card>
            <x-filament::table>
                <x-slot name='header'>
                    <x-filament::table-header-cell>SN</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Customer</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Channel</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Amount</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Paid</x-filament::table-header-cell>
                    <x-filament::table-header-cell>Order ID</x-filament::table-header-cell>
                </x-slot>

                <x-slot name='body'>";

        foreach ($unpaidOrders as $index => $order) {
            $output .= "<x-filament::table-row>
                            <x-filament::table-cell>{{ $index + 1 }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->customer->name ?? 'unselected' }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->channel->channel ?? 'unselected' }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->items->sum('price') }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->payments->sum('paid') }}</x-filament::table-cell>
                            <x-filament::table-cell>{{ $order->id }}</x-filament::table-cell>
                        </x-filament::table-row> ";
        }
        $output .= " </x-slot>
            </x-filament::table>
        </x-filament::card>
    </div>";

        return $output;
    }

    public function unpaidOrders()
    {
        $unpaidOrders = Order::whereNotIn('id', Order::full_payment())
            ->where('status', '!=', 'failed')
            ->whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->get();

        $output = '<div style="overflow-x:auto;">';
        $output .= '<table style="width:100%; border-collapse: collapse;">';
        $output .= '<thead style="background-color: #333; color: #fff;">';
        $output .= '<tr>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">SN</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Customer</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Channel</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Amount</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Paid</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Balance</th>';
        $output .= '<th style="border: 1px solid #ddd; padding: 8px;">Order ID</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        $sn = 1;
        foreach ($unpaidOrders as $order) {
            $customerName = $order->customer->name ?? '<i>unselected</i>';
            $orderChannel = $order->channel->channel ?? '<i>unselected</i>';
            $orderAmount = $order->items->sum('price');
            $amountPaid = $order->payments->sum('paid');
            $balance = $orderAmount - $amountPaid;

            $output .= '<tr style="border: 1px solid #ddd;">';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $sn++ . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $customerName . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $orderChannel . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($orderAmount, 2) . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($amountPaid, 2) . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . number_format($balance, 2) . '</td>';
            $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $order->id . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }


    public function load_table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('product_category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('stockHistories.stock_level')
                    ->label('Opening stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stockHistories.supply')
                    ->numeric()
                    ->label('Supply')
                    ->sortable(),
                TextColumn::make('Total')
                    ->numeric()
                    ->label('Total')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('items_sum_quantity')->sum('items', 'quantity')
                //     ->numeric()
                //     ->label('Sold')
                //     ->sortable(),
                TextColumn::make('items_sum_quantity')->sum([
                    'items' => fn (Builder $query) => $query->where('package_number', 1),
                ], 'quantity')
                    ->label('Sold')
                    ->sortable(),
                TextColumn::make('stockHistories.closing')
                    ->numeric()
                    ->label('Closing stock')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('date')
                //     ->date()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    public function loadReport()
    {

        // Default to previous day
        $this->start_date = Carbon::yesterday()->toDateString();
        $this->end_date = Carbon::today()->toDateString();

        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $this->report = Product::all()->map(function ($product) use ($startDate, $endDate) {
            // Opening Stock
            $openingStock = StockHistory::where('product_id', $product->id)
                ->where('date', '<', $startDate)
                ->latest('date')
                ->first()
                ->closing_stock ?? $product->quantity;

            // New Received
            $newReceived = StockHistory::where('product_id', $product->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('supply');

            // Quantity Sold
            $quantitySold = OrderItem::where('product_id', $product->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');

            // Closing Stock
            $closingStock = ($openingStock + $newReceived) - $quantitySold;

            return [
                'product_name' => $product->name,
                'opening_stock' => $openingStock,
                'new_received' => $newReceived,
                'quantity_sold' => $quantitySold,
                'closing_stock' => $closingStock,
            ];
        });
    }

    // public function render()
    // {
    //     return view('filament.pages.stock-management', [
    //         'report' => $this->report,
    //         'start_date' => $this->start_date,
    //         'end_date' => $this->end_date,
    //     ]);
    // }
}
