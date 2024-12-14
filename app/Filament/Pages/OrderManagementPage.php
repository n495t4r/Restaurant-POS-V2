<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OrderManagementPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Order Management Page';
    protected static string $view = 'filament.pages.order-management-page';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Order Management Page';
    }
}