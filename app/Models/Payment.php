<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'customer_id',
        'payment_method_id',
        'payment_methods',
        'paid',
        'status',

    ];

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

      /**
     * Find the paid sum for a specified date. Default param will return sum of all payments today
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
        //return sum of all payments today
        if($payment_method_id == 0){
            return self::whereDate('created_at', $date)
            ->sum('paid');
        }
        return self::where('payment_method_id', $payment_method_id)
            ->whereDate('created_at', $date)
            ->sum('paid');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
