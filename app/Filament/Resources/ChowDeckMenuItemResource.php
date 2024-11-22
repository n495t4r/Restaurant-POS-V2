<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChowDeckMenuItemResource\Pages;
use App\Models\ChowDeck\MenuItem as ChowDeckMenuItem;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ChowDeckMenuItemResource extends Resource
{
    protected static ?string $model = ChowDeckMenuItem::class;
    protected static ?string $navigationLabel = 'Chow Deck Menu';
    protected static ?string $slug = 'chowdeck-menu';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(65535),
                TextInput::make('price')
                    ->required()
                    ->numeric(),
                // TextInput::make('price_description')
                //     ->maxLength(255),
                Toggle::make('in_stock'),
                Toggle::make('is_published'),
                TextInput::make('category')
                    ->required()
                    ->maxLength(255),
                // TextInput::make('menu_category_id')
                //     ->required()
                //     ->numeric(),
                // TextInput::make('rank')
                //     ->numeric(),
            ]);
    }
    
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('description'),
                TextColumn::make('price'),
                TextColumn::make('reference'),
                TextColumn::make('category')
                ->sortable(),
                IconColumn::make('in_stock')
                ->boolean(),
                IconColumn::make('is_published')
                ->boolean()
                ->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChowDeckMenuItems::route('/'),
            'edit' => Pages\EditChowDeckMenuItem::route('/{record}/edit'),
            'create' => Pages\CreateChowDeckMenuItem::route('/create'),


        ];
    }
}