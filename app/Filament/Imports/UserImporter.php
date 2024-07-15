<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->requiredMapping()
                ->rules(['numeric','required']),
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            // ImportColumn::make('email_verified_at')
            //     ->helperText('yyyy/mm/dd hh:mm:ss eg. 2024/10/28 12:58:43')
            //     ->rules(['date'])
            //     ->castStateUsing(function (string $state): ?string {
            //         if (blank($state) || $state == 0 || $state == 'NULL') {
            //             return 'null';
            //         }
            //         return $state;
            //     }),
            ImportColumn::make('password')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('created_at')
                ->helperText('yyyy/mm/dd hh:mm:ss eg. 2024/10/28 12:58:43')
                ->rules(['date']),
            ImportColumn::make('updated_at')
                ->helperText('yyyy/mm/dd hh:mm:ss eg. 2021/12/28 18:38:43')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): ?User
    {
        return User::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'email' => $this->data['email'],
        ]);

        return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
