<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialResource\Pages;
use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
// use Filament\Resources\Form;
use Filament\Resources\Resource;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RawMaterialResource extends Resource
{
    protected static ?string $model = RawMaterial::class;
    protected static ?string $navigationGroup = 'Kitchen';
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\Select::make('category')
                    ->options([
                        'vegetable' => 'Vegetable',
                        'meat' => 'Meat',
                        'dairy' => 'Dairy',
                        'grain' => 'Grain',
                        'spice' => 'Spice',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('unit_of_measurement')
                    ->options([
                        'kg' => 'Kilogram',
                        'g' => 'Gram',
                        'ltr' => 'Liter',
                        'ml' => 'Milliliter',
                        'pcs' => 'Pieces',
                        'cup' => 'Cup',
                        'tbsp' => 'Tablespoon',
                        'tsp' => 'Teaspoon',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('unit_cost')
                    // ->required()
                    ->prefix('₦')
                    ->numeric(),
                Forms\Components\TextInput::make('stock_quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('reorder_level')
                    ->required()
                    ->numeric(),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),
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
                Tables\Columns\TextColumn::make('id')->label('Item ID'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category')->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                ->suffix(fn ($record) => ' '.$record->unit_of_measurement),
                Tables\Columns\TextColumn::make('unit_cost')->money('NGN'),
                Tables\Columns\TextColumn::make('reorder_level'),
                Tables\Columns\IconColumn::make('is_active')->boolean()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.first_name')
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Created by'),
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

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListRawMaterials::route('/'),
    //         'create' => Pages\CreateRawMaterial::route('/create'),
    //         'edit' => Pages\EditRawMaterial::route('/{record}/edit'),
    //     ];
    // }    


    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRawMaterials::route('/'),
        ];
    }
}
