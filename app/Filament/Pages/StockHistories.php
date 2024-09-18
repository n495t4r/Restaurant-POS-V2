<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\NewStock;
use App\Models\StockHistory;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class StockHistories extends Page implements HasTable
{
    use InteractsWithTable, InteractsWithPageFilters;

    protected static ?string $navigationIcon = 'heroicon-c-square-3-stack-3d';
    protected static string $view = 'filament.pages.stock-histories';    
    protected static ?string $title = 'Stock History';
    protected static ?string $slug = 'stock-hst';

    public $filterData = [
        'startDate' => null,
        'endDate' => null,
    ];

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
            ->label('Date filter')
                ->form([
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate'),
                ])
                ->action(function (array $data): void {
                    $this->filterData = $data;
                }),

            Action::make('closeStore')
                ->label('Close Store')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(fn () => $this->isStoreClosedToday())
                ->action(function () {
                    try {

                         // Check for unpaid orders
                         $unpaidOrders = Order::whereNotIn('id', Order::full_payment())
                         ->whereNotIn('id', Order::failed_order())
                         ->whereNotIn('id', Order::staff_order())
                         ->whereNotIn('id', Order::glovo_order())
                         ->whereNotIn('id', Order::chowdeck_order())
                         ->whereDate('created_at', today())
                         ->where(function ($query) {
                            $query->whereNull('customer_id')
                                  ->orWhere('customer_id', 14);
                        })
                         ->get();

                     if ($unpaidOrders->isNotEmpty()) {
                         Notification::make()
                            ->title('Please select customer names for unpaid orders before closing the store.')
                            ->color('warning')
                            ->send();
                         
                         return;
                     }

                        DB::transaction(function () {
                            $products = Product::all();
                            $closingStock = $products->map(function ($product) {
                                return [
                                    'product_id' => $product->id,
                                    'closing_qty' => $product->quantity,
                                ];
                            })->toArray();

                            $data['closing_date'] = now();
                            $data['closing_stock'] = json_encode($closingStock);

                    
                            StockHistory::create($data);

                            Order::whereDate('created_at', today())
                                ->whereNotIn('status', [0, 1])
                                ->update(['status' => 1]);
                        });


                        Notification::make()
                            ->title('Store closed successfully')
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title('Error closing store: ' . $e->getMessage())
                            ->color('danger')
                            ->send();
                    }
                }),
        ];
    }

    protected function isStoreClosedToday(): bool
    {
        return StockHistory::whereDate('closing_date', today())->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                // TextColumn::make('id')
                //     ->label('Prod ID')
                //     ->sortable(),
                TextColumn::make('product_category.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('NA')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opening_stock')
                    ->label('Opening stock')
                    ->toggleable(isToggledHiddenByDefault: false)
                    // ->sortable()
                    ->getStateUsing(fn($record) => $this->getOpeningStock($record)),
                TextColumn::make('supply')
                    ->label('Supply')
                    ->toggleable(isToggledHiddenByDefault: false)
                    // ->sortable()
                    ->getStateUsing(fn($record) => $this->getSupply($record)),
                TextColumn::make('items_sum_quantity')
                ->sum('items', 'quantity')
                    ->label('Sold')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->getStateUsing(fn($record) => $this->getSold($record)),
                TextColumn::make('closing_stock')
                    ->label('Closing stock')
                    ->toggleable(isToggledHiddenByDefault: false)
                    // ->sortable()
                    ->getStateUsing(fn($record) => $this->getClosingStock($record)),
            ])
            ->defaultSort('items_sum_quantity', 'desc')
            ->filters([

                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->options(function (): array {
                        return ProductCategory::all()->pluck('name', 'id')->all();
                    })
                    ->query(function (Builder $query, $state): Builder {

                        // Get the selected category ID
                        $selectedCategoryId = $state;

                        // Find all category IDs including the selected one and its children
                        $categoryIds = ProductCategory::where('parent_id', $selectedCategoryId)
                            ->orWhere('id', $selectedCategoryId)
                            ->pluck('id')
                            ->toArray();

                        // Apply the filter if categoryIds is not empty
                        if (!empty($categoryIds)) {
                            $query->whereIn('product_category_id', $categoryIds);
                        }

                        return $query;
                    }),
            ]);
    }

    protected function getOpeningStock($record)
    {
        $startDate = $this->filterData['startDate'] ?? today();

        $stock_history = StockHistory::getStockHistory($startDate);
        if ($stock_history) {
            $opening_stock = json_decode($stock_history->closing_stock, true);
            $filteredStock = collect($opening_stock)->firstWhere('product_id', $record->id);
            return $filteredStock ? $filteredStock['closing_qty'] : 0;
        }
        return 'No closing stock';
    }

    protected function getSupply($record)
    {
        $startDate = $this->filterData['startDate'] ?? today();
        $endDate = $this->filterData['endDate'] ?? today();

        return NewStock::where('product_id', $record->id)
            ->whereDate('created_at', '>=', date($startDate))
            ->whereDate('created_at', '<=', date($endDate))
            ->sum('quantity');
    }

    protected function getSold($record)
    {
        $startDate = $this->filterData['startDate'] ?? today();
        $endDate = $this->filterData['endDate'] ?? today();

        return $record->items()
            ->whereDate('created_at', '>=', date($startDate))
            ->whereDate('created_at', '<=', date($endDate))
            ->whereNotIn('order_id', Order::failed_order())
            ->sum('quantity');
    }

    protected function getClosingStock($record)
    {
        $endDate = $this->filterData['endDate'] ?? today();

        if ($endDate != today()) {
            $stock_history = StockHistory::getClosingStockHistory($endDate);
            if ($stock_history) {
                $opening_stock = json_decode($stock_history->closing_stock, true);
                $filteredStock = collect($opening_stock)->firstWhere('product_id', $record->id);

                return $filteredStock ? $filteredStock['closing_qty'] : 0;
            } else {
                return 'No closing stock';
            }
        }
        return $record->quantity;
    }
}
