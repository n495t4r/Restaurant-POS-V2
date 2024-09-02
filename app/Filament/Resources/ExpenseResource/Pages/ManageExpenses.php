<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Exports\ExpenseExporter;
use App\Filament\Imports\ExpenseImporter;
use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExpenses extends ManageRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->visible(auth()->user()->hasRole('super_admin'))
                ->label('Import expense')
                ->importer(ExpenseImporter::class),
            ExportAction::make()
                ->visible(auth()->user()->hasRole('super_admin'))
                ->exporter(ExpenseExporter::class)
                ->label('Export expense')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['user_id'] = auth()->id();

    //     return $data;
    // }
}
