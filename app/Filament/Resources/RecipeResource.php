<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Product;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
// use Filament\Resources\Form;
use Filament\Resources\Resource;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;
    protected static ?string $navigationGroup = 'Kitchen';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->options(function () {
                        return Product::whereDoesntHave('recipe')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('instructions')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('preparation_time')
                    ->required()
                    ->numeric()
                    ->suffix('minutes'),
                Forms\Components\TextInput::make('yield')
                    ->helperText('Total number of portions to produce')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('portion_size')
                    ->required()
                    ->numeric()
                    ->suffix('grams'),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),
                Forms\Components\Repeater::make('recipeItems')
                    ->relationship()
                    ->minItems(1)
                    ->schema([
                        Forms\Components\Select::make('raw_material_id')
                            ->relationship('rawMaterial', 'name')
                            ->searchable()
                            ->reactive()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->suffix(function (callable $get) {
                                $rawMaterialId = $get('raw_material_id');
                                if ($rawMaterialId) {
                                    $rawMaterial = \App\Models\RawMaterial::find($rawMaterialId);
                                    return $rawMaterial ? $rawMaterial->unit_of_measurement : '';
                                }
                                return '';
                            })
                            ->numeric(),
                    ])
                    ->columns(2),
                Forms\Components\Toggle::make('is_active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('preparation_time')
                    ->suffix(' minutes'),
                Tables\Columns\TextColumn::make('yield')
                    ->suffix(' portions'),
                    Tables\Columns\TextColumn::make('portion_size')
                    ->suffix(' grams'),
                Tables\Columns\TextColumn::make('user.first_name')->label('Created by')
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->boolean()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
            'view' => Pages\ViewRecipe::route('/{record}'),
        ];
    }
}
