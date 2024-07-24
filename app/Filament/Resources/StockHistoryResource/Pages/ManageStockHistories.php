<?php

namespace App\Filament\Resources\StockHistoryResource\Pages;

use App\Filament\Resources\StockHistoryResource;
use App\Models\Product;
use App\Models\StockHistory;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStockHistories extends ManageRecords
{
    protected static string $resource = StockHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                // ->mutateFormDataUsing(function (array $data): array {
                //     $products = Product::all();
                //     $closingStock = [];
                    
                //     $StockHistory = new StockHistory();

                //     foreach ($products as $product) {
                //         $closingStock[] = [
                //             'product_id' => $product->id,
                //             'closing_qty' => $product->quantity, // Assuming `quantity` is the column name
                //         ];
                //     }

                //     $data['closing_stock'] = json_encode($closingStock);
                //     $StockHistory->closing_date = $data['closing_date'];
                //     $StockHistory->closing_stock = $data['closing_stock'];
                //     $StockHistory->save();

                //     return $data;
                // })
                ->using(function (array $data) {
                    
                    $products = Product::all();
                    $closingStock = [];
                    
                    foreach ($products as $product) {
                        $closingStock[] = [
                            'product_id' => $product->id,
                            'closing_qty' => $product->quantity, // Assuming `quantity` is the column name
                        ];
                    }

                    $data['closing_stock'] = json_encode($closingStock);

                    return StockHistory::create($data);
                })

        ];
    }
}
