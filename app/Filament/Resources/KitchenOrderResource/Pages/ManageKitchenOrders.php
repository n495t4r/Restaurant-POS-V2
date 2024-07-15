<?php

namespace App\Filament\Resources\KitchenOrderResource\Pages;

use App\Filament\Resources\KitchenOrderResource;
use App\Models\Order;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;

class ManageKitchenOrders extends ManageRecords
{
    protected static string $resource = KitchenOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Order::query()->count())
                ->badgeColor('secondary'),
            'New' => Tab::make('New')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(Order::query()->where('status', 'pending')->count())
                ->badgeColor('warning'),
            'Rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(Order::query()->where('status', 'failed')->count())
                ->badgeColor('danger'),
            'Completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processed'))
                ->badge(Order::query()->where('status', 'failed')->count())
                ->badgeColor('success'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'New';
    }
}
