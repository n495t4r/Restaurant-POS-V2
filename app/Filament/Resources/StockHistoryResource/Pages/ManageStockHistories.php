<?php

namespace App\Filament\Resources\StockHistoryResource\Pages;

use App\Filament\Resources\StockHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStockHistories extends ManageRecords
{
    protected static string $resource = StockHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
