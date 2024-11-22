<?php

// namespace App\Filament\Pages;

// use App\Services\ChowdeckService;
// use App\Support\ApiQueryBuilder;
// use Filament\Forms\Form;
// use Filament\Pages\Page;
// use Filament\Tables;
// use Filament\Tables\Concerns\InteractsWithTable;
// use Filament\Tables\Contracts\HasTable;
// use Filament\Tables\Table;

// class Chowdeck extends Page implements HasTable
// {
//     use InteractsWithTable;

//     protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

//     protected static ?string $modelLabel = 'Menu Item';

//     protected static ?string $pluralModelLabel = 'Menu Items';

//     protected static string $view = 'filament.pages.chowdeck';

//     protected ChowdeckService $chowdeckService;

//     public function mount(ChowdeckService $chowdeckService): void
//     {
//         $this->chowdeckService = $chowdeckService;
//     }

//     protected function getTableQuery(): ApiQueryBuilder
//     {
//         $menuItems = $this->chowdeckService->getMenuItems();
//         return new ApiQueryBuilder($menuItems);
//     }

//     public function table(Table $table): Table
//     {
//         return $table
//             ->query($this->getTableQuery())
//             ->columns([
//                 Tables\Columns\TextColumn::make('name')
//                     ->searchable(),
//                 Tables\Columns\TextColumn::make('description')
//                     ->limit(50),
//                 Tables\Columns\IconColumn::make('in_stock')
//                     ->boolean(),
//                 Tables\Columns\IconColumn::make('is_published')
//                     ->boolean(),
//                 Tables\Columns\TextColumn::make('price')
//                     ->money('NGN')
//                     ->sortable(),
//                 Tables\Columns\TextColumn::make('category.name')
//                     ->label('Category'),
//                 Tables\Columns\ImageColumn::make('images.0.path')
//                     ->label('Image')
//                     ->circular(),
//             ])
//             ->filters([
//                 // You can add filters here if needed
//             ])
//             ->actions([
//                 Tables\Actions\ViewAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     // You can add bulk actions here if needed
//                 ]),
//             ]);
//     }
// }