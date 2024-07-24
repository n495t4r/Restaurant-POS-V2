<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\OrderItem;
use Carbon\Carbon;

class StockManagementService
{
    public function getStockReport($startDate = null, $endDate = null)
    {
        // Define default filter period as current day
        $startDate = $startDate ?? Carbon::now()->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $products = Product::all();
        $report = [];

        foreach ($products as $product) {
            // Get opening stock (closing stock of previous period or product stock level)
            $openingStock = $this->getOpeningStock($product->id, $startDate);

            // Get new received stock
            $newReceived = StockHistory::where('product_id', $product->id)
                // ->where('type', 'in')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('stock_level');

            // Get quantity sold
            $quantitySold = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->sum('quantity');

            // Calculate closing stock
            $closingStock = ($openingStock + $newReceived) - $quantitySold;

            $report[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'opening_stock' => $openingStock,
                'new_received' => $newReceived,
                'quantity_sold' => $quantitySold,
                'closing_stock' => $closingStock,
            ];
        }

        return $report;
    }

    private function getOpeningStock($productId, $startDate)
    {
        // Get the last closing stock before the start date
        $lastClosingStock = StockHistory::where('product_id', $productId)
            // ->where('type', 'out')
            ->where('date', '<', $startDate)
            ->latest('date')
            ->first();

        if ($lastClosingStock) {
            return $lastClosingStock->stock_level;
        }

        // If no closing stock, use the product stock level
        $product = Product::find($productId);
        return $product->quantity;
    }
}
