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
        if ($date == 'yesterday') {
            $date = now();
        } else {
            $date = now();
        }
        //return sum of all payments today
        if ($payment_method_id == 0) {
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

    public static function unpaid_amount($startDate, $endDate)
    {

        $amount = OrderItem::whereNotIn('order_id', Order::failed_order())
            ->whereNotIn('order_id', Order::staff_order())
            ->whereNotIn('order_id', Order::glovo_order())
            ->whereNotIn('order_id', Order::chowdeck_order())
            ->whereNotIn('order_id', Order::full_payment())
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('price');

            $orders_of_interest = OrderItem::whereNotIn('order_id', Order::failed_order())
            ->whereNotIn('order_id', Order::staff_order())
            ->whereNotIn('order_id', Order::glovo_order())
            ->whereNotIn('order_id', Order::chowdeck_order())
            ->whereNotIn('order_id', Order::full_payment())
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->pluck('order_id', 'order_id');

        $partial =  self::whereIn('order_id', $orders_of_interest)
            // ->whereIn('order_id', Order::partial_payment())
            // ->whereDate('created_at', '>=', $startDate)
            // ->whereDate('created_at', '<=', $endDate)
            ->sum('paid');

        return floatval($amount) - floatval($partial);
    }

    public static function staff_amount($startDate, $endDate)
    {

        $amount = OrderItem::whereNotIn('order_id', Order::failed_order())
            ->whereIn('order_id', Order::staff_order())
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('price');

           

        return floatval($amount);
    }
}
