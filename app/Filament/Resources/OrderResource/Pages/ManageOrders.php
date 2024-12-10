<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Imports\OrderImporter;
use App\Filament\Pages\StockHistories;
use App\Filament\Resources\NewStockResource;
use App\Filament\Resources\NewStockResource\Pages\ManageNewStocks;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\PaymentResource\Pages\ManagePayments;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Actions\ActionGroup;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('Manage stock')
                ->label('Manage stock')
                // ->visible(auth()->user()->hasRole('super_admin'))
                ->form(
                    function (Form $form) {
                        return NewStockResource::form($form);
                    }
                )->using(
                    function (array $data) {
                        return ManageNewStocks::new_stock($data);
                    }
                ),
                PaymentResource::makePaymentAction()
                ->color('success'),

            ActionGroup::make([
            Action::make('closeStore')
                ->label('Close cashier unit')
                ->color('danger')
                ->requiresConfirmation()
                ->disabled(fn() => StockHistories::isCashierUnitClosed())
                ->action(function () {
                    if (!StockHistories::isCashierUnitClosed()) {
                        StockHistories::closeCashierUnit();
                    }
                }),
            ExportAction::make()
                ->exporter(OrderExporter::class)
                // ->label('Export orders')
                ->formats([
                    ExportFormat::Xlsx,
                    // ExportFormat::Csv,
                ])
            
        ])];
        
    }
}
