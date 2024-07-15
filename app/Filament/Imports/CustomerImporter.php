<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->rules(['numeric','required']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255','nullable'])
                // ->castStateUsing(function (string $state): ?string {
                //     if (blank($state) || $state == 0 || $state == 'NULL') {
                //         return null;
                //     }
                //     return $state;
                // })
                ,
            ImportColumn::make('phone')
                ->rules(['required', 'max:15','unique:customers']),
            ImportColumn::make('address')
                ->rules(['max:255']),
            ImportColumn::make('avatar')
                ->rules(['max:255']),
            ImportColumn::make('user_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('created_at')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        return Customer::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'id' => $this->data['id'],
        ]);

        return new Customer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
