<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Imports\OrderImporter;
use App\Filament\Resources\NewStockResource;
use App\Filament\Resources\NewStockResource\Pages\ManageNewStocks;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('Manage stock')
            ->visible(auth()->user()->hasRole('super_admin'))
            ->form( function (Form $form){
                return NewStockResource::form($form);
            }
            )->using(
                function (array $data) {
                    return ManageNewStocks::new_stock($data);
                }
            )
            ,
            ImportAction::make()
            ->visible(auth()->user()->hasRole('super_admin'))

                    ->importer(OrderImporter::class),
            ExportAction::make()
                ->exporter(OrderExporter::class)
                // ->label('Export orders')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
        ];
    }
}
