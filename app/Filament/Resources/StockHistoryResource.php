<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockHistoryResource\Pages;
use App\Filament\Resources\StockHistoryResource\RelationManagers;
use App\Models\NewStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockHistory;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockHistoryResource extends Resource
{
    protected static ?string $model = Product::class;

    // protected static ?string $model = StockHistory::class;


    protected static ?string $modelLabel = 'Stock History';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        $products = Product::get();

        $products2 = [];
        foreach ($products as $product) {
            // $formattedString = $product->name . ' | N' . $product->price . ' | ' . $product->quantity;
            $formattedString = $product->name;
            $products2[$product->id] = $formattedString;
        }

        return $form
            ->model(StockHistory::class)
            ->schema([
                Forms\Components\DatePicker::make('closing_date')
                    ->label('Closing Date')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll(60)
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Prod_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_category.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('NA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    // ->formatStateUsing(fn (string $state): string => __("12"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('counter')
                    ->label('Opening stock')
                    ->formatStateUsing(function ($record) {
                        $stock_history = StockHistory::getPreviousDayRecords();
                        if ($stock_history) {
                            $opening_stock = json_decode($stock_history->closing_stock, true);
                            $filteredStock = collect($opening_stock)->firstWhere('product_id', $record->id);

                            return $filteredStock ? $filteredStock['closing_qty'] : 0;
                        } else {
                            return 'No closing stock';
                        }
                        // return $paymentDifference;
                    })
                    // ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supply')
                    ->formatStateUsing(function ($record){
                        // dd($record->id);
                        $supply = NewStock::where('product_id', $record->id)->sum('quantity');
                        return $supply;
                    })
                    ->default(0)
                    ->sortable(),
                // Tables\Columns\TextColumn::make('price')
                //     ->label('Total')
                // ->formatStateUsing(fn (string $state, Get $get): int => 
                // ($get('id') 
                // + $get('supply')) )
                // ->sortable(),
                // Tables\Columns\TextColumn::make('items_sum_quantity')->sum('items', 'quantity')
                //     ->numeric()
                //     ->label('Sold')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('items_sum_quantity')->sum([
                    'items' => function (Builder $query) {
                        // Filter items where the related order status is not 'failed'
                        $query->whereDate('created_at', now())
                            ->whereHas('order', function (Builder $query) {
                                $query->where('status', '!=', 'failed');
                            });
                    },
                ], 'quantity')
                    ->label('Sold')
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Closing stock')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('date')
                //     ->date()
                //     ->sortable(),
            ])
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
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['from'],
                //             fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                //         )
                //         ->when(
                //             $data['until'],
                //             fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                //         );
                // })
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
            'index' => Pages\ManageStockHistories::route('/'),
        ];
    }
}
