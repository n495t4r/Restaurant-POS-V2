<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CancelledOrders extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
        ->query(
            Order::query()->latest()->where('status','failed')
        )
        ->columns([
            Tables\Columns\TextColumn::make('id')
            ->label('Order Id'),
            Tables\Columns\TextColumn::make('customer.first_name') //change to customer.name instead
            ->toggleable(isToggledHiddenByDefault: false)
            ->label('Customer'),
            Tables\Columns\TextColumn::make('payments.amount')
            ->money('NGN')
            ->label('Amount')
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable(),
            Tables\Columns\TextColumn::make('payments.payment_methods')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Payment Method'),
            Tables\Columns\TextColumn::make('status')
                ->toggleable(isToggledHiddenByDefault: false)
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'null' => 'gray',
                    'pending' => 'warning',
                    'processed' => 'success',
                    'failed' => 'danger',
                    default => 'secondary'
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('D d H:i:s')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
        ])
        ->paginated(5);
    }
}
