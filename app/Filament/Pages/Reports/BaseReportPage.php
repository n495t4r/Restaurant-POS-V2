<?php

namespace App\Filament\Pages\Reports;

use App\DTO\StockReportDTO;
use Filament\Pages\Page;

abstract class BaseReportPage extends Page
{
    public string $startDate = '';
    public string $endDate = '';
    public bool $reportLoaded = false;

    abstract protected function buildReport(array $columns): StockReportDTO;

    abstract public function exportCSV();

    abstract public function exportPDF();

    public function mount(): void
    {
        $this->loadDefaultDateRange();
    }

    protected function loadDefaultDateRange(): void
    {
        $this->startDate = now()->subDay()->toDateString();
        $this->endDate = now()->toDateString();
    }

    protected function getToggledColumns(): array
    {
        return $this->getTable();
    }

    public function loadReportData(): void
    {
        unset($this->report);
        $this->reportLoaded = true;
    }
}
