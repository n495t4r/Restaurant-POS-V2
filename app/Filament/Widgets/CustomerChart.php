<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CustomerChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected static ?string $heading = 'Customer Income';

    protected function getData(): array
    {
        $customers = Customer::with('orders.payments')->get();
    
        $customerTotals = $customers->map(function ($customer) {
            $totalAmount = $customer->orders->flatMap(function ($order) {
                // Ensure that payments is a collection, default to an empty collection if null
                return $order->payments ? $order->payments->pluck('amount') : collect();
            })->sum();
        
            return $totalAmount;
        });
    
        return [
            'datasets' => [
                [
                    'label' => 'Customer income',
                    'data' => $customerTotals->toArray(),
                ],
            ],
            'labels' => $customers->pluck('first_name'), // Assuming 'name' is the customer's name attribute
        ];
    }
    

    protected function getType(): string
    {
        return 'line';
    }
}
