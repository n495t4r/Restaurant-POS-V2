<?php

namespace App\Filament\Resources\KitchenOrderResource\Pages;

use App\Filament\Resources\KitchenOrderResource;
use App\Models\Order;
use Carbon\Carbon;
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
                ->badge(Order::query()->whereDate('created_at', Carbon::today())->count())
                ->badgeColor('secondary'),
            'New' => Tab::make('New')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 2))
                ->badge(Order::query()->where('status', 2)->count())
                ->badgeColor('warning'),
            'Rejected' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 0)->whereDate('created_at', Carbon::today()))
                ->badge(Order::query()->where('status', 0)->whereDate('created_at', Carbon::today())->count())
                ->badgeColor('danger'),
            'Completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 1)->whereDate('created_at', Carbon::today()))
                ->badge(Order::query()->where('status', 1)->whereDate('created_at', Carbon::today())->count())
                ->badgeColor('success'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'New';
    }
}
