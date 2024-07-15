<?php

namespace App\Filament\Resources\OrderitemResource\Pages;

use App\Filament\Exports\OrderitemExporter;
use App\Filament\Imports\OrderitemImporter;
use App\Filament\Resources\OrderitemResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderitems extends ListRecords
{
    protected static string $resource = OrderitemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->label('Import items')
                ->importer(OrderitemImporter::class),
            ExportAction::make()
                ->exporter(OrderitemExporter::class)
                ->label('Export items')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
