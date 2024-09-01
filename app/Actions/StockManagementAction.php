<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\StoreStock;
use Illuminate\Support\Facades\DB;

class StockManagementAction
{
    public function receiveInFridge(Product $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {
            // Update store stock
            $storeStock = $product->storeStock;
            $storeStock->quantity -= $quantity;
            $storeStock->save();

            // Update fridge stock
            $product->fridge_quantity += $quantity;
            $product->save();

            // Update today's stock history
            $this->updateStockHistory($product->id, 'store', -$quantity);
            $this->updateStockHistory($product->id, 'fridge', $quantity);
        });
    }

    public function receiveInStore(Product $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {
            // Update store stock
            $storeStock = $product->storeStock;
            $storeStock->quantity += $quantity;
            $storeStock->save();

            // Update today's stock history
            $this->updateStockHistory($product->id, 'store', $quantity);
        });
    }

    public function closeStock()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $todayHistory = StockHistory::where('closing_date', $today)->first();
        if (!$todayHistory) {
            // If no history exists for today, create one based on current stock levels
            $todayHistory = $this->createStockHistoryFromCurrentStock($today);
        }

        // Create tomorrow's opening stock based on today's closing stock
        StockHistory::create([
            'closing_stock' => $todayHistory->closing_stock,
            'closing_date' => $tomorrow,
        ]);
    }

    private function updateStockHistory($productId, $stockType, $quantityChange)
    {
        $today = now()->toDateString();
        $stockHistory = StockHistory::firstOrCreate(['closing_date' => $today]);

        $currentQuantity = $stockHistory->getStockForProduct($productId, $stockType) ?? 0;
        $newQuantity = $currentQuantity + $quantityChange;

        $stockHistory->setStockForProduct($productId, $stockType, $newQuantity);
        $stockHistory->save();
    }

    private function createStockHistoryFromCurrentStock($date)
    {
        $closingStock = [
            'store' => [],
            'fridge' => [],
        ];

        $products = Product::with('storeStock')->get();
        foreach ($products as $product) {
            $closingStock['store'][$product->id] = $product->storeStock->quantity;
            $closingStock['fridge'][$product->id] = $product->fridge_quantity;
        }

        return StockHistory::create([
            'closing_stock' => $closingStock,
            'closing_date' => $date,
        ]);
    }
}