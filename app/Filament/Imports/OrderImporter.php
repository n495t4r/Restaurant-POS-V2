<?php

namespace App\Filament\Imports;

use App\Models\Order;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class OrderImporter extends Importer
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->numeric(),
            ImportColumn::make('customer_id')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0 || $state == 'NULL') {
                        return null;
                    }
                    return $state;
                }),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer'])
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0 || $state == 'NULL') {
                        return null;
                    }
                    return $state;
                }),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('commentForCook')
                ->rules(['max:255']),
            ImportColumn::make('reason')
                ->rules(['max:255']),
            ImportColumn::make('channel_id')
            ->castStateUsing(function (string $state): ?int {
                if (blank($state) || $state == 0 || $state == 'NULL') {
                    return null;
                }
                return $state;
            }),
            // ImportColumn::make('payment_method_id')
            // ->castStateUsing(function (string $state): ?int {
            //     if (blank($state) || $state == 0 || $state == 'NULL') {
            //         return null;
            //     }
            //     return $state;
            // }),
            ImportColumn::make('created_at')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Order
    {
        return Order::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        return new Order();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your order import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
