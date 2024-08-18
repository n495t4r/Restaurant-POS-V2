<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'title', 'amount', 'date', 'description','user_id', 'payment_method_id'
     ];


    protected $casts = [
        'date' => 'date', // Ensures the 'date' attribute is cast to a date type
    ];

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

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

      /**
     * Find the expense sum for a specified date. Default param will return sum of all expenses today
     *
     * @param int $payment_method_id
     * 1 => Cash
     * 2 => Transfer
     * 3 => ATM/POS
     * @param string $date eg. 'today', 'yesterday'
     * @return float
     */
    public static function sum_by_method(int $payment_method_id = 0, string $date = 'today'): float
    {
        if ($date == 'yesterday'){
            $date = now();
        }else{
            $date = now();
        }
        //return sum of all expenses today
        if($payment_method_id == 0){
            return self::whereDate('created_at', $date)
            ->sum('amount');
        }
        return self::where('payment_method_id', $payment_method_id)
            ->whereDate('created_at', $date)
            ->sum('amount');
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
