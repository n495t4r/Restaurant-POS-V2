<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield, InteractsWithPageFilters;

    protected static ?int $sort = 1;

    // public ?string $filter = 'month';
    public ?string $filter = 'year';

    protected function getStats(): array
    {


        $startDate = $this->filters['startDate'] ?? today();
        $endDate = $this->filters['endDate'] ?? today();

        return [
            Stat::make(
                label: 'Revenue',
                value: 'N ' .number_format(OrderItem::query()
                    ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                    ->whereNotIn('order_id', Order::failed_order())
                    ->sum('price'),2)
            )
                // ->description('Inclusive of N' . 
                // number_format(Payment::unpaid_amount($startDate, $endDate),2) . ' unpaid & N'
                // .number_format(Payment::staff_amount($startDate, $endDate),2) . ' staff orders')
                ->description('All sales')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Expense', 'N ' .number_format(Expense::totalExpense($startDate, $endDate),2))
                ->description('Inclusive of N'.number_format(Payment::staff_amount($startDate, $endDate),2) . ' staff orders')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            // Stat::make('Capital Expense', 'N ' . Expense::totalCapitalExpense($startDate, $endDate))
            //     ->chart([7, 2, 10, 3, 15, 4, 17])
            //     ->color('secondary'),

            Stat::make('Net Income', 'N ' . number_format(floatval(OrderItem::totalIncome($startDate, $endDate)) - floatval(Expense::totalExpense($startDate, $endDate)), 2))
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->description('Inclusive of N'.number_format(Payment::unpaid_amount($startDate, $endDate),2) . ' unpaid')
                ->color('secondary')
        ];
    }
}
