<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use App\Models\NewStock;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageNewStocks extends ManageRecords
{
    protected static string $resource = NewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    DB::beginTransaction();

                    try {
                        Product::increaseQuantity($data['product_id'], $data['quantity']);
                        // Commit the transaction
                        DB::commit();
                        return NewStock::create($data);
                    } catch (\Exception $e) {
                        // Rollback the transaction in case of an error
                        DB::rollBack();

                        // Optionally, rethrow the exception or handle it
                        throw $e;
                    }
                }),
        ];
    }
}
