<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MostOrdered extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Most Ordered Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->orderby('counter','desc')
                )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
            // Tables\Columns\TextColumn::make('category')
            //     ->label('Category')
            //     ->wrap()
            //     ->searchable(),
            Tables\Columns\TextColumn::make('price')
                ->money('NGN'),
            Tables\Columns\TextColumn::make('product_category.name'),
            Tables\Columns\TextColumn::make('counter')
                ->numeric()
                ->label('Count'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);
    }
}
