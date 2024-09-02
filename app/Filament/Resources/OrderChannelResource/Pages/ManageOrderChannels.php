<?php

namespace App\Filament\Resources\OrderChannelResource\Pages;

use App\Filament\Exports\OrderChannelExporter;
use App\Filament\Imports\OrderChannelImporter;
use App\Filament\Resources\OrderChannelResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderChannels extends ManageRecords
{
    protected static string $resource = OrderChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = auth()->id();
         
                return $data;
            })
            ->successNotificationTitle('Order channel created'),

            ImportAction::make()
            ->visible(auth()->user()->hasRole('super_admin'))

                ->label('Import channel')
                ->importer(OrderChannelImporter::class),
            ExportAction::make()
            
                ->exporter(OrderChannelExporter::class)
                ->label('Export channel')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }

   
}
