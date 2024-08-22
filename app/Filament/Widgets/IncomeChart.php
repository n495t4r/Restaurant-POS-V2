<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Income';
    protected static ?int $sort = 2;
    
    public ?string $filter = 'month';

    protected function getFilters(): ?array
{
    return [
        'today' => 'Today',
        'week' => 'This week',
        'month' => 'This month',
        'year' => 'This year',
    ];
}
// public static function canView(): bool
// {
//     return auth()->id() == 2;

//     // return auth()->user()->isAdmin();
// }

protected function getData(): array
{
    $data = [];

    switch ($this->filter) {
        case 'today':
            $data = Trend::model(OrderItem::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('price');
                $labels = $data->map(function (TrendValue $value) {
                    return date('h A', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });                
            break;
        case 'week':
            $data = Trend::model(OrderItem::class)
                ->between(
                    start: now()->startOfWeek(),
                    end: now()->endOfWeek(),
                )
                ->perDay()
                ->sum('price');
                $labels = $data->map(function (TrendValue $value) {
                    return date('D', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });
            break;
        case 'month':
            $data = Trend::model(OrderItem::class)
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->sum('price');
            $labels = $data->map(function (TrendValue $value) {
                    return date('M D d', strtotime($value->date)); // Format as Month Day (e.g., "Jan Tue 01")
                });                
            break;
        case 'year':
            $data = Trend::model(OrderItem::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now(),
                )
                ->perMonth()
                ->sum('price');
            $labels = $data->map(function (TrendValue $value) {
                    return date('M Y', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });
                
            break;
        case 'custom':
            // Assuming you have start and end date parameters available from the Livewire component
            $startDate = $this->customStartDate; // Replace with your Livewire property name
            $endDate = $this->customEndDate; // Replace with your Livewire property name
        
            // Example: Assuming $startDate and $endDate are Carbon instances
            $data = Trend::model(OrderItem::class)
                ->between(
                    start: $startDate->startOfDay(),
                    end: $endDate->endOfDay(),
                )
                ->perDay()
                ->sum('price');
            $labels = $data->map(fn (TrendValue $value) => $value->date);
            break;
    }

    return [
        'datasets' => [
            [
                'label' => 'Income',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $labels,
    ];
}



    protected function getType(): string
    {
        return 'line';
    }
}
