<?php

namespace App\Filament\Resources\OrderitemResource\Pages;

use App\Filament\Resources\OrderitemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderitem extends EditRecord
{
    protected static string $resource = OrderitemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
