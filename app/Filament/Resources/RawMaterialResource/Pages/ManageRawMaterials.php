<?php

namespace App\Filament\Resources\RawMaterialResource\Pages;

use App\Filament\Resources\RawMaterialResource;
use ArielMejiaDev\FilamentPrintable\Actions\PrintAction;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRawMaterials extends ManageRecords
{
    protected static string $resource = RawMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            PrintAction::make(),
        ];
    }
}
