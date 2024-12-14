<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Infolists\Components\RepeatableEntry;
// use Filament\Forms\Components\Repeater;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;

class ViewRecipe extends ViewRecord
{
    protected static string $resource = RecipeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Recipe Details')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Product Name')
                            ->size('text-2xl font-bold'),
                        TextEntry::make('yield')
                            ->label('Yield')
                            ->suffix(' portions'),
                        TextEntry::make('portion_size')
                            ->label('Portion Size')
                            ->suffix(' grams'),
                        TextEntry::make('preparation_time')
                            ->label('Preparation Time')
                            ->suffix(' minutes'),
                    ])
                    ->columns([
                        'xs' => 2,
                        'sm' => 4,
                        'md' => 4,
                        'xl' => 5,
                    ]),

                Section::make('Ingredients')
                    ->collapsed(true)
                    ->schema([
                        RepeatableEntry::make('recipeItems')
                            ->schema([
                                TextEntry::make('rawMaterial.name')
                                    ->label('Ingredient'),
                                TextEntry::make('quantity')
                                    ->suffix(fn ($record) => $record->rawMaterial->unit_of_measurement),
                            ])
                            ->columns([
                                'xs' => 3,
                                'sm' => 3,
                                'md' => 3,
                                'xl' => 3,
                            ]),
                    ]),

                Section::make('Instructions')
                    ->collapsed(false)
                    ->schema([
                        TextEntry::make('instructions')
                            ->label('Step by step guide')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('user.first_name')
                            ->label('Created By'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns([
                        'xs' => 3,
                        'sm' => 3,
                        'md' => 3,
                        'xl' => 3,
                    ]),
            ]);
    }
}

