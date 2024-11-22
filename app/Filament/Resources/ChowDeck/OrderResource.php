<?php

namespace App\Filament\Resources\ChowDeck;

use App\Filament\Resources\ChowDeck\OrderResource\Pages;
use App\Filament\Resources\ChowDeck\OrderResource\RelationManagers;
use App\Models\ChowDeck\ChowDeckOrder;
use ArielMejiaDev\FilamentPrintable\Actions\PrintBulkAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = ChowDeckOrder::class;

    // protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Chow Deck Orders';
    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('reference')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('summary')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('source')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('class')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('created_at'),
                Forms\Components\DateTimePicker::make('updated_at'),
                Forms\Components\TextInput::make('delivery_price')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('time_payment_confirmed'),
                Forms\Components\DateTimePicker::make('time_customer_received_order'),
                Forms\Components\DateTimePicker::make('actual_delivery_time'),
                Forms\Components\KeyValue::make('customer'),
                Forms\Components\KeyValue::make('items'),
                Forms\Components\KeyValue::make('timeline'),
                Forms\Components\KeyValue::make('customer_address'),
                Forms\Components\KeyValue::make('vendor_address'),
                Forms\Components\KeyValue::make('vendor_information'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
        // ->recordUrl(
        //     fn (Model $record): string => route('/', ['record' => $record]),
        // )
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('total_price'),
                TextColumn::make('status'),
              TextColumn::make('reference'),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime(),
                TextColumn::make('customer_address'),
    
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => OrderResource\Pages\ListOrders::route('/'),
            'view' => OrderResource\Pages\ViewOrder::route('/{record}'),
        ];
    }    
}
