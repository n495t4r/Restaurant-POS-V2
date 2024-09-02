<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Exports\PaymentExporter;
use App\Filament\Imports\PaymentImporter;
use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePayments extends ManageRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
            ->visible(auth()->user()->hasRole('super_admin'))

            ->importer(PaymentImporter::class),
            ExportAction::make()
                ->exporter(PaymentExporter::class)
                // ->label('Export orders')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
