<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['key'] = Str::random(64);
        return $data;
    }

    // protected function afterCreate(): void
    // {
    //     $this->notify('success', 'API key created successfully. Please copy the key now as it won\'t be shown again.');
    // }
}
