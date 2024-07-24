<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Reports\StockManagement;
use App\Infolists\Components\ReportEntry;
use App\Models\Product;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;

class StockReports extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-document-report';

    protected static string $view = 'filament.pages.stock-reports';

    public ?array $data = [];
    
    protected static ?string $model = Product::class;

    public Product $product;

    // public function mount(): void{
    //     if($record = $this->model->name){
    //         $this->reportsInfolist->fill($record->data);
    //     }

    // }

    public function reportsInfolist(Infolist $infolist): Infolist
    { 
        // dd($this->view);

        return $infolist
            ->state([])
            
            ->schema([

                Section::make('Stock Reports')
                    ->aside()
                    ->description('View detailed reports about your stock levels, transactions, and more.')
                    ->extraAttributes(['class' => 'stock-report-card'])
                    ->schema([
                        ReportEntry::make('stock_management')
                            ->hiddenLabel()
                            ->heading('Stock Management')
                            ->description('View and manage stock levels, received quantities, and sold quantities.')
                            // ->icon('heroicon-o-clipboard-list')
                            ->iconColor(Color::Blue)
                            ->url(StockManagement::getUrl()),
                            TextEntry::make('name')
                            ->label('Order status')
                            ->badge()
                            ->formatStateUsing(function ($record) {
                               return 'Product Name';
                            }),

                    ]),
            ]);
    }
}
