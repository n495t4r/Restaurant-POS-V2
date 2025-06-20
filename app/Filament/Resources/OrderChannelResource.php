<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderChannelResource\Pages;
use App\Filament\Resources\OrderChannelResource\RelationManagers;
use App\Models\OrderChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderChannelResource extends Resource
{
    protected static ?string $model = OrderChannel::class;

    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\TextInput::make('channel')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->onColor('success')
                    ->offColor('danger')
                    // ->inline()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('channel')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->sortable()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrderChannels::route('/'),
        ];
    }
}
