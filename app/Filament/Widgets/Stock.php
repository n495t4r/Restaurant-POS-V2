<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Stock extends BaseWidget
{
    protected static ?int $sort = 8;

    public function table(Table $table): Table
    {
        return $table
        ->query(
            Product::query()->orderby('quantity','asc')
            )
        ->columns([
            Tables\Columns\TextColumn::make('name')
            ->searchable(),
            // Tables\Columns\TextColumn::make('category')
            //     ->label('Category')
            //     ->wrap()
            //     ->searchable(),
            Tables\Columns\TextColumn::make('quantity')
                ->numeric(),
            Tables\Columns\TextColumn::make('product_category.name'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);
    }
}
