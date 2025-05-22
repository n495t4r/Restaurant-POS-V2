<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\DateRangeSelect;
use App\Filament\Resources\NewStockResource\Pages;
use App\Filament\Resources\NewStockResource\RelationManagers;
use App\Models\NewStock;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class NewStockResource extends Resource
{
    protected static ?string $model = NewStock::class;
    protected ?string $subheading = 'Custom Page Subheading';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-start-on-rectangle';


    public static function form(Form $form): Form
    {

        $locations = ['Shop front', 'Store', 'Market', 'Kitchen'];
        $toLocations = ['Shop front', 'Store', 'Kitchen', 'Retire'];  // Excluding 'Market'
        $products = Product::where('status', true)->pluck('name', 'id');

        $super_adminRole = auth()->user()->hasRole('super_admin');
        $managerRole = auth()->user()->hasRole('Manager');
        $cookRole = auth()->user()->hasRole('Cook');
        $cashierRole = auth()->user()->hasRole('Cashier');

        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->searchable()
                    // ->relationship('product', 'name')
                    ->options(
                        $products
                    )
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                Forms\Components\Select::make('from')
                    ->label('From')
                    // ->options(collect($locations)->mapWithKeys(fn($item) => [$item => $item]))
                    ->options(function (callable $get) use ($locations, $managerRole, $cookRole, $cashierRole) {

                        if ($cookRole){
                            unset($locations[1]); // Remove Store option
                            unset($locations[2]); // Remove Market option
                        }

                        if ($cashierRole){
                            unset($locations[0]); // Remove from Shop front option
                            unset($locations[3]); // Remove from Kitchen option
                            unset($locations[2]); // Remove from Market
                        }

                        return collect($locations)
                            // ->reject(fn($item) => $item === $get('from'))
                            ->mapWithKeys(fn($item) => [$item => $item]);
                    })
                    ->required()
                    // ->default($locations[1])

                    // ->disableOptionWhen(function (string $value) use ($locations, $cashierRole) {
                    //     if ($value === $locations[0] || $value === $locations[3]) {
                    //         return $cashierRole;
                    //         // return !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Cook');
                    //     }
                    //     return false;
                    // })
                    ->live(),

                Forms\Components\Select::make('to')
                    ->label('To')
                    ->options(function (callable $get) use ($toLocations, $cookRole, $super_adminRole, $cashierRole) {

                        if ($cookRole || $cashierRole){
                            unset($toLocations[1]); // Remove send to store option
                        }

                        if (!$super_adminRole){
                            unset($toLocations[3]); // Remove Retire option
                        }

                        if ($cashierRole){
                            unset($toLocations[2]); // Remove send to Kitchen option
                        }

                        return collect($toLocations)
                            ->reject(fn($item) => $item === $get('from'))
                            ->mapWithKeys(fn($item) => [$item => $item]);
                    })
                    ->required()
                    // ->default($toLocations[0])
                    ->disabled(fn(callable $get) => ! $get('from'))
                    // ->disableOptionWhen(function (string $value) use ($toLocations) {
                    //     if ($value === $toLocations[2]) {
                    //         return !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Cook');
                    //     }
                    //     return false;
                    // })
                    ->live(),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),
                Forms\Components\DatePicker::make('supply_date')
                    ->default(now())
                    ->visible(fn() => auth()->user()->hasRole('super_admin'))  //control using permissions instead - change later auth()->user()->hasPermission('super_admin')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('product.product_category.id')
                    ->label('Category')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('NA')
                    ->formatStateUsing(function ($state) {
                        // $product = Product::find($record->product_id);
                        $product_category = ProductCategory::where('id', $state)->first();

                        if ($product_category) {
                            // dd($product_category->name);1

                            return $product_category->name;
                        }
                        return 'NA';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity(Portion')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('supply_date')
                    ->sortable()
                    ->date()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('from')
                    // ->badge()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('to')
                    // ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Created by')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('supply_date', 'desc')
            ->filters([

                // SelectFilter::make('product.product_category.id')
                //     // ->relationship('product', 'product_category_id')
                //     ->label('Category')
                //     ->options(function (): array {
                //         return ProductCategory::all()->pluck('name', 'id')->all();
                //     })
                //     ->query(function (Builder $query, $state): Builder {

                //         // Get the selected category ID
                //         $selectedCategoryId = $state;

                //         // Find all category IDs including the selected one and its children
                //         $categoryIds = ProductCategory::where('parent_id', $selectedCategoryId)
                //             ->orWhere('id', $selectedCategoryId)
                //             ->pluck('id')
                //             ->toArray();

                //         // Apply the filter if categoryIds is not empty
                //         if (!empty($categoryIds)) {
                //             $query->whereIn('product_category_id', $categoryIds);
                //         }

                //         return $query;
                //     })

                DateRangeFilter::make('supply_date')
                    ->label('Supply date')
                    // ->timezone('UTC')
                    //Default Start Date
                    ->startDate(Carbon::now())
                    //Default EndDate
                    ->endDate(Carbon::now())
                    ->firstDayOfWeek(1),

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
            'index' => Pages\ManageNewStocks::route('/'),
        ];
    }
}
