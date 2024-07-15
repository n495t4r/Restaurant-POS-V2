<?php

namespace App\Filament\Resources\ExpenseCategoryResource\Pages;

use App\Filament\Exports\ExpenseCategoryExporter;
use App\Filament\Imports\ExpenseCategoryImporter;
use App\Filament\Resources\ExpenseCategoryResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->label('Import category')
                ->importer(ExpenseCategoryImporter::class),
            ExportAction::make()
                ->exporter(ExpenseCategoryExporter::class)
                ->label('Export category')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
