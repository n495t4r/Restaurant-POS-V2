<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    
 
    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(Order::query()->count())
                ->badgeColor('warning'),
            'In-Kitchen' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(Order::query()->where('status', 'pending')->count())
                ->badgeColor('warning'),
            'Cancelled' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(Order::query()->where('status', 'failed')->count())
                ->badgeColor('danger'),
            'Completed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processed'))
                ->badge(Order::query()->where('status', 'failed')->count())
                ->badgeColor('success'),
            'POS' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%POS%');
                }))->badge(Order::whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%POS%');
                })->count())
                ->badgeColor('info'),
            'Cash' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%Cash%');
                }))->badge(Order::whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%Cash%');
                })->count())
                ->badgeColor('info'),
            'Transfer' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%Transfer%');
                }))->badge(Order::whereHas('pay_method', function ($query) {
                    $query->where('name', 'like', '%Transfer%');
                })->count())
                ->badgeColor('info'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
