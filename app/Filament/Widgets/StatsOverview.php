<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class StatsOverview extends BaseWidget
{
        use HasWidgetShield;

    protected static ?int $sort = 1;

    // public ?string $filter = 'month';
    public ?string $filter = 'year';

    protected function getStats(): array
    {
        return [
            Stat::make('Income', 'N '.OrderItem::totalIncome($this->filter))
            ->description('N'.OrderItem::totalfailed($this->filter).' failed orders')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
            Stat::make('Expense', 'N '.Expense::totalExpense($this->filter))
            ->description('N'.Expense::totalOperationalExpense($this->filter).' Operational Expenses')
            ->color('danger'),
            Stat::make('Capital Expense', 'N '.Expense::totalCapitalExpense($this->filter))
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('secondary'),
            Stat::make('Net Income', 'N ' . (floatval(OrderItem::totalIncome($this->filter)) - floatval(Expense::totalOperationalExpense($this->filter))))
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('secondary')
        ];
    }
}
