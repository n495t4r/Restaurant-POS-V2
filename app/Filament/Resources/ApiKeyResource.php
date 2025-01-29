<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Filament\Resources\ApiKeyResource\RelationManagers;
use App\Models\ApiKey;
use ArielMejiaDev\FilamentPrintable\Actions\PrintBulkAction;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'API Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    Forms\Components\Toggle::make('is_active')
                        ->required()
                        ->default(true)
                        ->columnSpan(1),
                    // Forms\Components\Select::make('user_id')
                    //     ->relationship('user', 'first_name')
                    //     ->required()
                    //     ->searchable()
                    //     ->columnSpan(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('key')
                ->searchable()
                ->copyable()
                ->toggleable(),
            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->sortable(),
            Tables\Columns\TextColumn::make('last_used_at')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active'),
            SelectFilter::make('user')
                ->relationship('user', 'first_name')
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('stats')
                ->icon('heroicon-o-chart-bar')
                // ->url(fn (ApiKey $record): string => route('filament.resources.api-keys.stats', $record))
        ])
        ->bulkActions([
            Tables\Actions\BulkAction::make('activate')
                ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check'),
            Tables\Actions\BulkAction::make('deactivate')
                ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-x-mark'),
            Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
            'view' => Pages\ViewApiKey::route('/{record}'),
            'stats' => Pages\ApiKeyStats::route('/{record}/stats'),
        ];
    }
}
