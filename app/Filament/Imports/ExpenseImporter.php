<?php

namespace App\Filament\Imports;

use App\Models\Expense;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ExpenseImporter extends Importer
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            // ImportColumn::make('id')
            //     ->requiredMapping()
            //     ->numeric()
            //     ->rules(['required', 'integer']),
            ImportColumn::make('category_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('user')
                ->requiredMapping()
                ->numeric()
                // ->resolveUsing('user_id')
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('payment_method_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('description'),
            ImportColumn::make('created_at')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Expense
    {
        // return Expense::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Expense();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your expense import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
