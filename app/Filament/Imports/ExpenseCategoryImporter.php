<?php

namespace App\Filament\Imports;

use App\Models\ExpenseCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ExpenseCategoryImporter extends Importer
{
    protected static ?string $model = ExpenseCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('created_at')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?ExpenseCategory
    {
        // return ExpenseCategory::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ExpenseCategory();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your expense category import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
