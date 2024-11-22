<?php

namespace App\Filament\Resources\ChowDeck\OrderResource\Pages;

use App\Filament\Resources\ChowDeck\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use ArielMejiaDev\FilamentPrintable\Actions\PrintAction;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            PrintAction::make(),
        ];
    }
}
