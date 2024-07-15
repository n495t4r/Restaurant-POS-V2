<?php

namespace App\Filament\Imports;

use App\Models\Payment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PaymentImporter extends Importer
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->rules(['numeric', 'required']),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('order_id')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0) {
                        return null;
                    }
                    return $state;
                })
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('user_id')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0) {
                        return null;
                    }
                    return $state;
                })
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('customer_id')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0) {
                        return null;
                    }
                    return $state;
                })
                // ->requiredMapping()
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('payment_method_id')
                ->castStateUsing(function (int $state): ?int {
                    if (blank(trim($state)) || trim($state) == 0) {
                        return null;
                    }
                    return (int) trim($state);
                })
                ->requiredMapping()
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('payment_methods'),
            ImportColumn::make('paid')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('status'),
            ImportColumn::make('created_at')
                ->helperText('yyyy/mm/dd hh:mm:ss eg. 2024/10/28 12:58:43')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->helperText('yyyy/mm/dd hh:mm:ss eg. 2021/12/28 18:38:43')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Payment
    {
        return Payment::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        return new Payment();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your payment import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
