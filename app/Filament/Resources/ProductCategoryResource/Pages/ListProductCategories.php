<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Exports\ProductCategoryExporter;
use App\Filament\Imports\ProductCategoryImporter;
use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->label('Import category')
                ->visible(auth()->user()->hasRole('super_admin'))

                ->importer(ProductCategoryImporter::class),
            ExportAction::make()
                ->exporter(ProductCategoryExporter::class)
                ->label('Export category')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
