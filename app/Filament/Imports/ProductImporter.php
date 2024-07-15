<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->rules(['numeric','required']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('image')
                ->rules(['max:255']),
            ImportColumn::make('price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('quantity')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('product_category')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0 ) {
                        return null;
                    }
                    return $state;
                })
                ->relationship(),
            ImportColumn::make('status')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('counter')
                ->rules(['integer']),
            ImportColumn::make('created_at')
            ->helperText('Format to yyyy/mm/dd hh:mm:ss eg. 2024/10/28 12:58:43')
            ->rules(['date']),
            ImportColumn::make('updated_at')
            ->helperText('Format to yyyy/mm/dd hh:mm:ss eg. 2021/12/28 18:38:43')
            ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        return Product::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        // return new Product();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    // public function getValidationMessages(): array
    // {
    //     return [
    //         'created_at.date' => 'created_at.date error',
    //         'updated_at.date' => 'created_at.date error'

    //     ];
    // }

    public static function getOptionsFormComponents(): array
    {
        return [
            Checkbox::make('updateExisting')
                ->label('Update existing records'),
        ];
    }
}
