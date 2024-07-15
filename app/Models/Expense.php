<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'title', 'amount', 'date', 'description','user_id'
     ];


    protected $casts = [
        'date' => 'date', // Ensures the 'date' attribute is cast to a date type
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function formattedAmount()
    {
        return number_format($this->amount, 2);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public static function totalExpense2($categoryId, $filter)
    {
        $query = self::where('category_id', $categoryId);

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
                // No filter, return total expense for all time
        }

        return $query->sum('amount');
    }

    public static function totalExpense($filter='month')
{
    $query = self::query();
    
    switch ($filter) {
        case 'day':
            $query->whereDate('date', today());
            break;
        case 'week':
            $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereYear('date', now()->year)
                  ->whereMonth('date', now()->month);
            break;
        case 'year':
            $query->whereYear('date', now()->year);
            break;
        default:
            // No filter, retrieve total income without time filtering
    }

    $totalExpense = $query->sum('amount');

    return number_format($totalExpense, 2);
}

    public static function totalOperationalExpense($filter)
{
    $query = self::whereHas('category', function ($query) {
        $query->where('name', 'not like', '%Capital%');
    });
    
    switch ($filter) {
        case 'day':
            $query->whereDate('date', today());
            break;
        case 'week':
            $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereYear('date', now()->year)
                  ->whereMonth('date', now()->month);
            break;
        case 'year':
            $query->whereYear('date', now()->year);
            break;
        default:
            // No filter, retrieve total income without time filtering
    }

    $totalExpense = $query->sum('amount');
    return number_format($totalExpense, 2);

}

public static function totalCapitalExpense($filter)
{
    $query = self::whereHas('category', function ($query) {
        $query->where('name', 'like', '%Capital%');
    });

     switch ($filter) {
        case 'day':
            $query->whereDate('date', today());
            break;
        case 'week':
            $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereYear('date', now()->year)
                  ->whereMonth('date', now()->month);
            break;
        case 'year':
            $query->whereYear('date', now()->year);
            break;
        default:
            // No filter, retrieve total income without time filtering
    }

    $totalExpense = $query->sum('amount');

    return number_format($totalExpense, 2);
}

public static function totalExpenseByCategory($filter)
{
    $query = self::select('category_id')
        ->selectRaw('SUM(amount) as total_amount')
        ->whereHas('category');

    switch ($filter) {
        case 'day':
            $query->whereDate('date', today());
            break;
        case 'week':
            $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereYear('date', now()->year)
                ->whereMonth('date', now()->month);
            break;
        case 'year':
            $query->whereYear('date', now()->year);
            break;
        default:
            // No filter, retrieve total expense by category without time filtering
    }

    return $query->groupBy('category_id')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->category->name => $item->total_amount];
        });
}


}
