<?php

namespace App\Filament\Imports;

use App\Models\ProductCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductCategoryImporter extends Importer
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->numeric(),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('parent')
                ->castStateUsing(function (string $state): ?int {
                    if (blank($state) || $state == 0 ) {
                        return null;
                    }
                    return $state;
                })
                ->requiredMapping()
                ->relationship(resolveUsing: 'id'),
            ImportColumn::make('created_at')
            ->rules(['date']),
            ImportColumn::make('updated_at')
            ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?ProductCategory
    {
        return ProductCategory::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        // return new ProductCategory();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product category import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
