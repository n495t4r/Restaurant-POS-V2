<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use App\Models\NewStock;
use App\Models\Product;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageNewStocks extends ManageRecords
{
    protected static string $resource = NewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add stock')
                ->using(
                    function (array $data) {
                        return self::new_stock($data);
                    }
                ),
        ];
    }

    public static function new_stock(array $data)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($data['product_id']);
            $quantity = $data['quantity'];

            // Decrease quantity from the 'from' location
            if ($data['from'] === 'Store') {
                if (!$product->decreaseQuantity($product->id, $quantity, true)) {
                    Notification::make()
                        ->title('Not enough quantity in Store.')
                        ->danger()
                        // ->duration(5000)
                        ->send();

                    return;
                    // throw new \Exception("Not enough quantity in Store.");
                }
            } else if ($data['from'] === 'Shop front') {
                if (!$product->decreaseQuantity($product->id, $quantity)) {
                    Notification::make()
                    ->title('Not enough quantity in Shop front.')
                    ->danger()
                    // ->duration(5000)
                    ->send();

                return;                }
            }

            // Increase quantity in the 'to' location
            if ($data['to'] === 'Store') {
                $product->increaseQuantity($product->id, $quantity, true);
            } else {
                $product->increaseQuantity($product->id, $quantity);
            }

            $newStock = NewStock::create($data);
            DB::commit();
            return $newStock;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Optionally, rethrow the exception or handle it
            throw $e;
        }
    }
}
