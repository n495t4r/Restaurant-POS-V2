<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenOrderResource\Pages;
use App\Filament\Resources\KitchenOrderResource\RelationManagers;
use App\Models\KitchenOrder;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Tabs;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Illuminate\Database\Eloquent\Model;


class KitchenOrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'Kitchen Display';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Kitchen';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('details')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_done')
                    ->required(),
            ]);
    }



    public static function table(Table $table): Table
    {

        return $table
            // ->query(Order::query()->where('created_at', now()))
            ->poll('5s')
            ->paginated([12, 24, 48, 96, 'all'])
            ->columns([
                Split::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('id')
                            ->numeric()
                            ->label('Order ID')
                            ->prefix('Order ID: ')
                            ->copyMessage('ID copied')
                            ->copyMessageDuration(1500)
                            ->copyable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('commentForCook')
                            ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                            ->color('primary')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: false),
                    ]),
                ]),
                Panel::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('Orders')
                            // ->state(fn (Order $record): string => $record->items[0]->quantity)
                            ->state(function (Model $record): array {
                                $itemNames = [];

                                // Loop through each item associated with the record
                                foreach ($record->items as $item) {
                                    // Get the product name for the current item
                                    $productName = $item->product->name;

                                    // Get the quantity for the current item
                                    $itemQuantity = $item->quantity;

                                    // Concatenate the product name and quantity
                                    $itemNames[] = "$itemQuantity" . "x " . "$productName";
                                }

                                // Join the array of item names into a single string separated by commas
                                // return implode(', ', $itemNames);
                                return $itemNames;
                            })
                            ->listWithLineBreaks(),
                    ]),

                ])->collapsible(),
            ])->defaultSort('id', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at'),
                    ]),
            ], layout: FiltersLayout::AboveContentCollapsible)

            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageKitchenOrders::route('/'),
        ];
    }
}
