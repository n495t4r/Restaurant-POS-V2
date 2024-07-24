<?php
// app/Filament/Pages/StockManagement.php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\OrderItem;
use App\Models\StockHistory;
use Filament\Pages\Page;
use Carbon\Carbon;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockManagement extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';
    protected static string $view = 'filament.pages.stock-management';
    protected static ?string $title = 'Stock Management';
    protected static ?string $navigationLabel = 'Stock Management';
    protected static ?string $slug = 'stock-management';

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

    public function reportsInfolist(Infolist $infolist): Infolist
    {
        // dd($this->view);
        $product = Product::all()->toArray();
        // dd($this->prod);

        return $infolist
            ->state($product)

            ->schema([

                Section::make('Stock Reports')
                    ->aside()
                    ->description('View detailed reports about your stock levels, transactions, and more.')
                    
                    ->schema([
                        TextEntry::make('product')
                            ->label('Order status')
                            ->badge()
                            ->formatStateUsing(function ($record) {
                                dd(Product::all()->toArray());
                                return 'Product Name';
                            }),

                    ]),
            ]);
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
