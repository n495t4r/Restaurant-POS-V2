<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class StockHistory extends Model
{
    protected $fillable = [
        'closing_stock',
        'closing_date',
    ];

    protected $casts = [
        'closing_stock' => 'array',
        'closing_date' => 'date',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'closing_stock', 'product_id', 'closing_qty');
    }

    public function getStockForProduct($productId, $stockType)
    {
        return $this->closing_stock[$stockType][$productId] ?? null;
    }

    public function setStockForProduct($productId, $stockType, $quantity)
    {
        if (!isset($this->closing_stock[$stockType])) {
            $this->closing_stock[$stockType] = [];
        }
        $this->closing_stock[$stockType][$productId] = $quantity;
    }

    public static function getStockHistory($date = null)
    {
        // If a $date is provided, subtract one day from it
        if ($date) {
            $closingDate = Carbon::parse($date)->subDay()->toDateString();
        } else {
            // If no date is provided, default to yesterday
            $closingDate = Carbon::yesterday()->toDateString();
        }

        // Return the stock history for the calculated closing date
        return self::whereDate('closing_date', '=', $closingDate)->first();
    }

    public static function getClosingStockHistory($date)
    {
        $closingDate = Carbon::parse($date)->toDateString();

        // Return the stock history for the calculated closing date
        return self::whereDate('closing_date', '=', $closingDate)->first();
    }
}
