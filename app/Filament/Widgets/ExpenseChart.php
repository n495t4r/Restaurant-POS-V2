<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class ExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Expense';
    protected static ?int $sort = 3;

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
    
    protected function getData(): array
    {

        $data = [];

    switch ($this->filter) {
        case 'today':
            $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
                $query->select('id')
                    ->from('expense_categories')
                    ->where('name', 'like', '%Capital%');
            }))
                ->dateColumn('date')
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('amount');
                $labels = $data->map(function (TrendValue $value) {
                    return date('h A', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });                
            break;
        case 'week':
            $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
                $query->select('id')
                    ->from('expense_categories')
                    ->where('name', 'like', '%Capital%');
            }))
                ->dateColumn('date')
                ->between(
                    start: now()->startOfWeek(),
                    end: now()->endOfWeek(),
                )
                ->perDay()
                ->sum('amount');
                $labels = $data->map(function (TrendValue $value) {
                    return date('D', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });
            break;
        case 'month':
            $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
                $query->select('id')
                    ->from('expense_categories')
                    ->where('name', 'like', '%Capital%');
            }))
                ->dateColumn('date')
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->sum('amount');
            $labels = $data->map(function (TrendValue $value) {
                    return date('M D d', strtotime($value->date)); // Format as Month Day (e.g., "Jan Tue 01")
                });                
            break;
        case 'year':
            $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
                $query->select('id')
                    ->from('expense_categories')
                    ->where('name', 'like', '%Capital%');
            }))
                ->dateColumn('date')
                ->between(
                    start: now()->startOfYear(),
                    end: now(),
                )
                ->perMonth()
                ->sum('amount');
            $labels = $data->map(function (TrendValue $value) {
                    return date('M Y', strtotime($value->date)); // Format as Month Day (e.g., "Jan 01")
                });
                
            break;
        case 'custom':
            // Assuming you have start and end date parameters available from the Livewire component
            $startDate = $this->customStartDate; // Replace with your Livewire property name
            $endDate = $this->customEndDate; // Replace with your Livewire property name
        
            // Example: Assuming $startDate and $endDate are Carbon instances
            $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
                $query->select('id')
                    ->from('expense_categories')
                    ->where('name', 'like', '%Capital%');
            }))
                ->dateColumn('date')
                ->between(
                    start: $startDate->startOfDay(),
                    end: $endDate->endOfDay(),
                )
                ->perDay()
                ->sum('amount');
            $labels = $data->map(fn (TrendValue $value) => $value->date);
            break;
    }


    return [
        'datasets' => [
            [
                'label' => 'Expense',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $labels,
    ];

        // $data = Trend::query(Expense::whereNotIn('category_id', function ($query) {
        //     $query->select('id')
        //         ->from('expense_categories')
        //         ->where('name', 'like', '%Capital%');
        // }))
        //     ->dateColumn('date')
        //     ->between(
        //         start: now()->startOfYear(),
        //         end: now(),
        //     )
        //     ->perDay()
        //     ->sum('amount');
    
        // return [
        //     'datasets' => [
        //         [
        //             'label' => 'Expense',
        //             'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
        //         ],
        //     ],
        //     'labels' => $data->map(fn (TrendValue $value) => $value->date),
        // ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
