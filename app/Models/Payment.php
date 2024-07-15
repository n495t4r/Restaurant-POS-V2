<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
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
        return $this->belongsTo(Order::class,'order_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
}
