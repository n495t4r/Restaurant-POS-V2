<?php

namespace App\Filament\Pages;

use App\Filament\Resources\NewStockResource;
use App\Filament\Resources\NewStockResource\Pages\ManageNewStocks;
use App\Models\Order;
use App\Models\NewStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockHistory;
use App\Models\StoreStock;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class StoreStocks extends Page implements HasTable
{
    use InteractsWithTable, InteractsWithPageFilters, HasPageShield;

    // protected static bool $shouldRegisterNavigation = $auth()->user()->hasRole('super_admin');

    protected static ?string $navigationIcon = 'heroicon-c-square-3-stack-3d';
    protected static string $view = 'filament.pages.store-stock';    
    protected static ?string $title = 'Store/Kitchen Stock';
    protected static ?string $slug = 'store-stock';

    public $filterData = [
        'startDate' => null,
        'endDate' => null,
    ];

//     public static function shouldRegisterNavigation(): bool
// {
//     $user = auth()->user();
    
//     // Check if user has the required role(s)
//     return $user->hasRole('super_admin') || $user->hasRole('Manager');
    
//     // Alternatively, if you want to check for multiple roles:
//     // return $user->hasAnyRole(['super_admin', 'manager', 'cashier']);
// }

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
                Actions\CreateAction::make('Manage stock')
                ->label('Add stock')
                // ->visible(auth()->user()->hasRole('super_admin'))
                ->form(
                    function (Form $form) {
                        return NewStockResource::form($form);
                    }
                )->using(
                    function (array $data) {
                        return ManageNewStocks::new_stock($data);
                    }
                ),
            Action::make('closeStore')
                ->label('Close Store')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(fn () => $this->isStoreClosedToday())
                ->action(function () {
                    try {
                        if (!StockHistories::isCashierUnitClosed()){
                            StockHistories::closeCashierUnit();
                        }

                        DB::transaction(function () {
                            $products = Product::all();
                            $closingStock = $products->map(function ($product) {
                                return [
                                    'product_id' => $product->id,
                                    'closing_qty' => $product->store,
                                ];
                            })->toArray();

                            $data['closing_date'] = now();
                            $data['closing_stock'] = json_encode($closingStock);

                    
                            StoreStock::create($data);

                            // Order::whereDate('created_at', today())
                            //     ->whereNotIn('status', [0, 1])
                            //     ->update(['status' => 1]);
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
        return StoreStock::whereDate('closing_date', today())->exists();
    }

    public function table(Table $table): Table
    {

        return $table
        ->striped()
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
                TextColumn::make('received')
                    ->label('Received')
                    ->toggleable(isToggledHiddenByDefault: false)
                    // ->sortable()
                    ->getStateUsing(fn($record) => $this->getSupply($record)),
                TextColumn::make('newstock_sum_quantity')
                ->sum('newstock', 'quantity')
                    ->label('To front')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable()
                    ->getStateUsing(fn($record) => $this->getSold($record)),
                TextColumn::make('closing_stock')
                    ->label('Closing stock')
                    ->toggleable(isToggledHiddenByDefault: false)
                    // ->sortable()
                    ->getStateUsing(fn($record) => $this->getClosingStock($record)),
            ])
            ->defaultSort('newstock_sum_quantity', 'desc')
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

        $stock_history = StoreStock::getStockHistory($startDate);
        if ($stock_history) {
            $opening_stock = json_decode($stock_history->closing_stock, true);
            $filteredStock = collect($opening_stock)->firstWhere('product_id', $record->id);
            return $filteredStock ? $filteredStock['closing_qty'] : 0;
        }
        return 'store not closed';
    }

    protected function getSupply($record)
    {
        $locations = ['Shop front', 'Store', 'Market', 'Kitchen'];

        $startDate = $this->filterData['startDate'] ?? today();
        $endDate = $this->filterData['endDate'] ?? today();

        return NewStock::where('product_id', $record->id)
            ->whereDate('created_at', '>=', date($startDate))
            ->whereDate('created_at', '<=', date($endDate))
            ->whereIn('to', [$locations[1], $locations[3]])
            ->orderBy('quantity')
            ->sum('quantity');
    }

    protected function getSold($record)
    {
        $locations = ['Shop front', 'Store', 'Market', 'Kitchen'];

        $startDate = $this->filterData['startDate'] ?? today();
        $endDate = $this->filterData['endDate'] ?? today();

        return NewStock::where('product_id', $record->id)
            ->whereDate('created_at', '>=', date($startDate))
            ->whereDate('created_at', '<=', date($endDate))
            ->whereIn('from', [$locations[1], $locations[3]])
            ->sum('quantity');
    }

    protected function getClosingStock($record)
    {
        $endDate = $this->filterData['endDate'] ?? today();

        if ($endDate != today()) {
            $stock_history = StoreStock::getClosingStockHistory($endDate);
            if ($stock_history) {
                $opening_stock = json_decode($stock_history->closing_stock, true);
                $filteredStock = collect($opening_stock)->firstWhere('product_id', $record->id);

                return $filteredStock ? $filteredStock['closing_qty'] : 0;
            } else {
                return 'No closing stock';
            }
        }
        return $record->store;
    }
}
