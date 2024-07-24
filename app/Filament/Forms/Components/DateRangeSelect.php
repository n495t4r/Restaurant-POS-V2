<?php

namespace App\Filament\Forms\Components;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;

class DateRangeSelect extends Select
{
    public string $startDateField = '';
    public string $endDateField = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->options($this->getDateRangeOptions())
            ->afterStateUpdated(function ($state, Set $set) {
                $this->updateDateRange($state, $set);
            });
    }

    public function startDateField(string $fieldName): static
    {
        $this->startDateField = $fieldName;

        return $this;
    }

    public function endDateField(string $fieldName): static
    {
        $this->endDateField = $fieldName;

        return $this;
    }

    public function getDateRangeOptions(): array
    {
        $currentDate = now();
        $earliestDate = Carbon::parse('first transaction date'); // replace with actual earliest date

        $options = [
            'Today' => 'Today',
            'Yesterday' => 'Yesterday',
            'Last 7 Days' => 'Last 7 Days',
            'Last 30 Days' => 'Last 30 Days',
            'This Month' => 'This Month',
            'Last Month' => 'Last Month',
            'Custom' => 'Custom',
        ];

        return $options;
    }

    public function updateDateRange($state, Set $set): void
    {
        match ($state) {
            'Today' => $this->setDateRange(now(), now(), $set),
            'Yesterday' => $this->setDateRange(now()->subDay(), now()->subDay(), $set),
            'Last 7 Days' => $this->setDateRange(now()->subDays(6), now(), $set),
            'Last 30 Days' => $this->setDateRange(now()->subDays(29), now(), $set),
            'This Month' => $this->setDateRange(now()->startOfMonth(), now(), $set),
            'Last Month' => $this->setDateRange(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth(), $set),
            default => $this->setDateRange(null, null, $set),
        };
    }

    public function setDateRange(?Carbon $start, ?Carbon $end, Set $set): void
    {
        $set($this->startDateField, $start?->format('Y-m-d'));
        $set($this->endDateField, $end?->format('Y-m-d'));
    }
}
