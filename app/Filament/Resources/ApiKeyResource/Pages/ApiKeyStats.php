<?php

// app/Filament/Resources/ApiKeyResource/Pages/ApiKeyStats.php
namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ApiKeyStats extends Page
{
    protected static string $resource = ApiKeyResource::class;
    protected static string $view = 'filament.resources.api-key.pages.stats';
    public $record;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getRequestStats()
    {
        // Daily requests for the last 30 days
        $dailyRequests = $this->record->apiRequests()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Endpoint usage statistics
        $endpointStats = $this->record->apiRequests()
            ->select('endpoint', DB::raw('count(*) as count'))
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Response code distribution
        $responseCodeStats = $this->record->apiRequests()
            ->select('response_code', DB::raw('count(*) as count'))
            ->groupBy('response_code')
            ->orderBy('response_code')
            ->get();

        return [
            'dailyRequests' => $dailyRequests,
            'endpointStats' => $endpointStats,
            'responseCodeStats' => $responseCodeStats,
        ];
    }
}