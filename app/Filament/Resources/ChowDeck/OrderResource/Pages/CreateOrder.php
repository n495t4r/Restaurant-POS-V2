<?php

namespace App\Filament\Resources\ChowDeck\OrderResource\Pages;

use App\Filament\Resources\ChowDeck\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
