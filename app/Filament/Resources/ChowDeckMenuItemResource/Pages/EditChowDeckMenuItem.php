<?php

namespace App\Filament\Resources\ChowDeckMenuItemResource\Pages;

use App\Filament\Resources\ChowDeckMenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChowDeckMenuItem extends EditRecord
{
    protected static string $resource = ChowDeckMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove any fields that shouldn't be sent to the API menu_category_id
        unset($data['id'], $data['image'], $data['reference'], $data['price_description'], $data['category'], $data['menu_category_id']);
        return $data;
    }
}
