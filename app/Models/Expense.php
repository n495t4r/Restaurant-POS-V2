<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'amount',
        'date',
        'description',
        'user_id',
        'payment_method_id'
    ];


    protected static function boot()
    {
        parent::boot();

        // Automatically set the current date if not provided
        static::creating(function ($model) {
            if (is_null($model->date)) {
                $model->date = Carbon::now(); // or use Carbon::today() if you want just the date
            }
        });
    }

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
    public static function sum_by_method(int $payment_method_id = 0, $startDate, $endDate): float
    {

        //return sum of all expenses today
        if ($payment_method_id == 0) {
            return self::whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('amount');
        }
        return self::where('payment_method_id', $payment_method_id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');
    }

    public static function totalExpense($startDate, $endDate)
    {
        $expense_amount = self::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');

        return $expense_amount;
    }

    public static function totalOperationalExpense($startDate, $endDate)
    {
        $query = self::whereHas('category', function ($query) {
            $query->where('name', 'not like', '%Capital%');
        });

        $totalExpense = $query->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');

        // $totalExpense = $query->sum('amount');
        return number_format($totalExpense, 2);
    }

    public static function totalCapitalExpense($startDate, $endDate)
    {
        $query = self::whereHas('category', function ($query) {
            $query->where('name', 'like', '%Capital%');
        });


        $totalExpense = $query->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');


        // $totalExpense = $query->sum('amount');
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
