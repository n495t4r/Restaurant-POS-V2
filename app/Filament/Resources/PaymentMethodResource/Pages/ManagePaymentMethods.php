<?php

namespace App\Filament\Resources\PaymentMethodResource\Pages;

use App\Filament\Exports\PaymentMethodExporter;
use App\Filament\Imports\PaymentMethodImporter;
use App\Filament\Resources\PaymentMethodResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePaymentMethods extends ManageRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
            ->importer(PaymentMethodImporter::class),
            ExportAction::make()
                ->exporter(PaymentMethodExporter::class)
                // ->label('Export orders')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
