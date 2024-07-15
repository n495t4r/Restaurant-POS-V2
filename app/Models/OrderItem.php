<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model 
{
    use HasFactory;

    protected $fillable =[
        'price',
        'quantity',
        'product_id',
        'order_id',
        'pack_id'=>1,
        'package_number',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'array',
            'quantity' => 'array',
        'product_id' => 'array',
        'order_id' => 'array',
        'package_number' => 'array',
        ];
    }
    
    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function totalIncome($filter)
    {
        $query = self::whereHas('order', function ($query) {
            $query->where('status', '!=', 'failed');
        });
    
        switch ($filter) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereYear('created_at', now()->year)
                      ->whereMonth('created_at', now()->month);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
            default:
                // No filter, retrieve total income without time filtering
        }
    
        $totalIncome = $query->sum('price');
    
        return number_format($totalIncome, 2);

    }
    

public static function totalfailed($filter)
{
    $query = self::whereHas('order', function ($query) {
        $query->where('status', '=', 'failed');
    });

    switch ($filter) {
        case 'day':
            $query->whereDate('created_at', today());
            break;
        case 'week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
            break;
        case 'year':
            $query->whereYear('created_at', now()->year);
            break;
        default:
            // No filter, retrieve total income without time filtering
    }
    
    $totalfailed = $query->sum('price');

    return number_format($totalfailed, 2);
}

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
