<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema());
    }

    static function getFormSchema(): array
    {
        return [

            // Forms\Components\Select::make('assigned_user_id')
            //     ->relationship('user', 'first_name')
            //     ->required(),
            Forms\Components\TextInput::make('name')
                ->maxLength(191),
            Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(191)
                ->default(null),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->unique(ignoreRecord: true)
                ->maxLength(15)
                ->required(!auth()->user()->hasRole('super_admin')),
            Forms\Components\TextInput::make('address')
                ->maxLength(191)
                ->default(null),
            Forms\Components\TextInput::make('avatar')
                ->maxLength(191)
                ->default(null),
            // Forms\Components\Select::make('user_id')
            //     ->relationship('creator', 'first_name')
            //     ->visible(auth()->user()->hasRole('super_admin'))
            //     ->required(),
            Forms\Components\Toggle::make('is_active')
                ->onColor('success')
                ->offColor('danger')
                ->default(true)
                ->visible(auth()->user()->hasRole('super_admin'))
                ->required(),
            Forms\Components\Toggle::make('is_staff')
                ->onColor('success')
                ->offColor('danger')
                ->default(false)
                ->visible(auth()->user()->hasRole('super_admin'))
                ->required(),
            
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Customer ID')->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer name')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('user.first_name')
                //     ->label('User name')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('avatar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Created by')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();
        // Allow viewing if it's the user's own record
        return $user->id == $record->user_id;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        // Hide from navigation if user can't view list
        return $user->can('view_customer');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
