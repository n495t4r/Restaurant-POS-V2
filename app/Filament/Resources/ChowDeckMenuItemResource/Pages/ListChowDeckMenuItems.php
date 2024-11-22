<?php

namespace App\Filament\Resources\ChowDeckMenuItemResource\Pages;

use App\Filament\Resources\ChowDeckMenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use ArielMejiaDev\FilamentPrintable\Actions\PrintAction;

class ListChowDeckMenuItems extends ListRecords
{
    protected static string $resource = ChowDeckMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            PrintAction::make(),
        ];
    }
}
