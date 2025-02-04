<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $navigationGroup = 'Manage';
    protected static ?string $model = Product::class;
    // protected static ?string $navigationGroup = 'Product';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static function is_admin():bool {
        return auth()->user()->hasRole('super_admin');
    }

    protected static function is_manager():bool {
        return auth()->user()->hasRole('Manager');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public') // Ensures it uses the 'public' disk
                    ->directory('products') // Specifies the subfolder 'products'
                    ->maxSize(1528) // Optional: You can set the maximum file size (e.g., 10MB)
                    ,
                Forms\Components\TextInput::make('price')
                    ->helperText('Selling price per unit/portion')
                    ->required()
                    ->numeric()
                    ->disabled(
                        function (){
                            if (self::is_admin() || self::is_manager()){
                                return false;
                            }
                            return true;
                        })
                    ->minValue(0)
                    ->prefix('NGN'),
                Forms\Components\Select::make('product_category_id')
                    ->relationship('product_category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Front quantity')
                    ->visible(self::is_admin())
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->numeric(),
                Forms\Components\TextInput::make('store')
                    ->label('Store quantity')
                    ->visible(self::is_admin())
                    ->default(0)
                    ->required()
                    ->minValue(0)
                    ->numeric(),
                Forms\Components\Toggle::make('status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Product id')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label('is active')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl(url('product.png'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_per_portion')
                    ->money('NGN')
                    ->sortable()
                    ->tooltip('Cost per portion based on recipe')
                    ->getStateUsing(fn(Product $record) => $record->cost_per_portion)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('recommended_selling_price')
                    ->label('Recomm. Price')
                    ->money('NGN')
                    ->sortable()
                    ->tooltip('Recommended selling price (30% markup)')
                    ->getStateUsing(fn(Product $record) => $record->recommended_selling_price)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('product_category.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

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
                //
            ])
            ->actions([
                Tables\Actions\Action::make('viewRecipe')
                    ->label('View Recipe')
                    ->icon('heroicon-o-book-open')
                    ->iconButton()
                    ->url(
                        fn(Product $record) => RecipeResource::getUrl('view', ['record' => $record->recipe]),
                        shouldOpenInNewTab: true
                    )
                    ->hidden(fn(Product $record) => $record->recipe === null),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
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
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
