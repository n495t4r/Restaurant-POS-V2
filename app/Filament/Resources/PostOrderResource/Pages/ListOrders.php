<?php

namespace App\Filament\Resources\PostOrderResource\Pages;

use App\Filament\Resources\PostOrderResource;
use App\Models\Order;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = PostOrderResource::class;

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
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%POS%');
                }))->badge(Order::whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%POS%');
                })->count())
                ->badgeColor('info'),
            'Cash' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%Cash%');
                }))->badge(Order::whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%Cash%');
                })->count())
                ->badgeColor('info'),
            'Transfer' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%Transfer%');
                }))->badge(Order::whereHas('payments', function ($query) {
                    $query->where('payment_methods', 'like', '%Transfer%');
                })->count())
                ->badgeColor('info'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
