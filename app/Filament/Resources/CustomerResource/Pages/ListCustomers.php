<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Exports\CustomerExporter;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
            ->visible(auth()->user()->hasRole('super_admin'))

                ->label('Import customer')
                ->importer(CustomerImporter::class),
            ExportAction::make()
                ->exporter(CustomerExporter::class)
                ->label('Export customer')
        ];
    }
}
