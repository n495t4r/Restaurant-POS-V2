<?php

namespace App\Filament\Resources\KitchenOrderResource\Pages;

use App\Filament\Resources\KitchenOrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Navigation\NavigationItem;
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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 2))
                ->badge(Order::query()->where('status', 2)->count())
                ->badgeColor('warning'),
            'Rejected' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 0))
                ->badge(Order::query()->where('status', 0)->count())
                ->badgeColor('danger'),
            'Completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 1))
                ->badge(Order::query()->where('status', 1)->count())
                ->badgeColor('success'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'New';
    }
}
