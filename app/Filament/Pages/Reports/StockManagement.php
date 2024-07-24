<?php

namespace App\Filament\Pages\Reports;

use App\DTO\StockReportDTO;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockManagement extends BaseReportPage
{
    protected static string $view = 'filament.pages.reports.stock-management';

    protected function buildReport(array $columns): StockReportDTO
    {
        $products = Product::all();

        $data = $products->map(function ($product) {
            $openingStock = $this->getOpeningStock($product);
            $newReceived = $this->getNewReceived($product);
            $qtySold = $this->getQuantitySold($product);
            $closingStock = ($openingStock + $newReceived) - $qtySold;

            return [
                'product' => $product->name,
                'opening_stock' => $openingStock,
                'new_received' => $newReceived,
                'qty_sold' => $qtySold,
                'closing_stock' => $closingStock,
            ];
        })->toArray();

        return new StockReportDTO($data, $columns);
    }

    protected function getOpeningStock($product)
    {
        $previousDay = now()->subDay();
        $lastClosingStock = DB::table('stock_histories')
            ->where('product_id', $product->id)
            ->whereDate('date', $previousDay)
            ->value('stock_level');

        return $lastClosingStock ?? $product->quantity;
    }

    protected function getNewReceived($product)
    {

        $supply = DB::table('stock_histories')
        ->where('product_id', $product->id)
        ->whereBetween('date', [$this->startDate, $this->endDate])       
        ->sum('supply');

    return $supply ?? $product->quantity;
    
        // return DB::table('received_items')
        //     ->where('product_id', $product->id)
        //     ->whereBetween('created_at', [$this->startDate, $this->endDate])
        //     ->sum('quantity');
    }

    protected function getQuantitySold($product)
    {
        return OrderItem::where('product_id', $product->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('quantity');
    }

    public function exportCSV()
    // : StreamedResponse
    {
        // Implement CSV export logic
    }

    public function exportPDF()
    // : StreamedResponse
    {
        // Implement PDF export logic
    }

    protected function getTable(): array
    {
        return [
            'product',
            'opening_stock',
            'new_received',
            'qty_sold',
            'closing_stock',
        ];
    }
}
