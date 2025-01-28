<?php

// app/Filament/Resources/ApiKeyResource/Pages/ViewApiKey.php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;

class ViewApiKey extends ViewRecord
{
    protected static string $resource = ApiKeyResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('API Key Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name'),
                            TextEntry::make('key')
                                ->copyable(),
                            TextEntry::make('user.name')
                                ->label('Owner'),
                            TextEntry::make('is_active')
                                ->badge()
                                ->color(fn ($state) => $state ? 'success' : 'danger'),
                            TextEntry::make('last_used_at')
                                ->dateTime(),
                            TextEntry::make('created_at')
                                ->dateTime(),
                        ]),
                    ]),
                Section::make('Usage Statistics')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('total_requests')
                                ->state(fn ($record) => $record->apiRequests()->count())
                                ->label('Total Requests'),
                            TextEntry::make('requests_today')
                                ->state(fn ($record) => $record->apiRequests()->whereDate('created_at', today())->count())
                                ->label('Requests Today'),
                            TextEntry::make('average_daily_requests')
                                ->state(function ($record) {
                                    $days = max(1, $record->created_at->diffInDays(now()) + 1);
                                    return round($record->apiRequests()->count() / $days, 2);
                                })
                                ->label('Avg. Daily Requests'),
                        ]),
                    ]),
            ]);
    }
}