<?php

namespace App\Filament\Imports;

use App\Models\Orderitem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class OrderitemImporter extends Importer
{
    protected static ?string $model = Orderitem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->numeric(),
            ImportColumn::make('price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('quantity')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('order')
                ->requiredMapping()
                ->numeric()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('product_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('package_number')
                ->numeric()
                ->rules(['integer']),
            // ImportColumn::make('created_at')
            //     ->helperText('yyyy/mm/dd hh:mm:ss eg. 2024/10/28 12:58:43')
            //     ->rules(['date']),
            // ImportColumn::make('updated_at')
            //     ->helperText('yyyy/mm/dd hh:mm:ss eg. 2021/12/28 18:38:43')
            //     ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Orderitem
    {
        return Orderitem::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        return new Orderitem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your orderitem import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
