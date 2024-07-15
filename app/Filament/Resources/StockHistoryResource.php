<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockHistoryResource\Pages;
use App\Filament\Resources\StockHistoryResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockHistory;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
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
    protected static ?string $modelLabel = 'Stock History';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    
    public static function form(Form $form): Form
    {
        $products = Product::get();
       
        $products2 = [];
        foreach ($products as $product) {
            $formattedString = $product->name . ' | N' . $product->price . ' | ' . $product->quantity;
            $products2[$product->id] = $formattedString;
        }

        
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('supply')
                    ->numeric()
                    ->default(null),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('stock_level')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockHistories.stock_level')
                    ->label('Opening stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockHistories.supply')
                    ->numeric()
                    ->label('Supply')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Total')
                    ->numeric()
                    ->label('Total')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('items_sum_quantity')->sum('items', 'quantity')
                //     ->numeric()
                //     ->label('Sold')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('items_sum_quantity')->sum([
                        'items' => fn (Builder $query) => $query->where('package_number', 1), 
                ], 'quantity')
                    ->label('Sold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockHistories.closing')
                    ->numeric()
                    ->label('Closing stock')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('date')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
