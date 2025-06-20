<?php

namespace App\Filament\Resources\ChowDeck\OrderResource\Pages;

use App\Filament\Resources\ChowDeck\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
