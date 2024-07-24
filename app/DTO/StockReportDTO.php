<?php

namespace App\DTO;

class ReportDTO
{
    public function __construct(
        public array $data
    ) {}
}

class StockReportDTO extends ReportDTO
{
    public function __construct(
        public array $data,
        public array $columns
    ) {
        parent::__construct($data);
    }
}
