<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpenseCat extends BaseWidget
{
    protected static ?int $sort = 7;

    protected static ?string $heading = 'Expense Category';

    public function table(Table $table): Table
    {
        // TextColumn::make('users_avg_age')->avg('users', 'age')
        return $table
        ->query(
            ExpenseCategory::query()
        )
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('expense_sum_amount')
                    ->sum('expense','amount')
                    ->label('Expense Amount')
                    ->money('NGN'),
                // Tables\Columns\TextColumn::make('description')
                //     ->wrap()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('expense.date')
                //     ->date(),
                // Tables\Columns\TextColumn::make('total_expense')
                // ->label('Total Expense')
                // ->value(function ($category) {
                //     return Expense::totalExpense2($category->id, 'month'); // Adjust filter as needed
                // })
                // ->money('NGN'),
            ])
                ->defaultSort('expense_sum_amount', 'desc');
    }
}
