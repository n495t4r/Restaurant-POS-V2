<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'channel_id',
        'payment_method_id',
        'user_id',
        'commentForCook',
        'status',
    ];

    // protected function casts(): array
    // {
    //     return [
    //         'items' => 'Array',
    //     ];
    // }

    public function items() : HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function packs()
    {
        return $this->hasMany(Pack::class);
    }
    
    // public function payments()
    // {
    //     return $this->belongsTo(Payment::class);
    // }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pay_method()
    {
        return $this->belongsTo(PaymentMethod::class,'payment_method_id');
    }

    public function channel()
    {
        return $this->belongsTo(OrderChannel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCustomerName()
    {
        if($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return 'Walk-in Customer';
    }

    // public function total()
    // {
    //     return $this->items->map(function ($i){
    //         return $i->price;
    //     })->sum();
    // }

    // public function formattedTotal()
    // {
    //     return number_format($this->total(), 2);
    // }

    // public function receivedAmount()
    // {
    //     return $this->payments->map(function ($i){
    //         return $i->amount;
    //     })->sum();
    // }

    public function payment_methods()
    {
        return $this->payments->map(function ($i){
            return json_decode($i->payment_methods);
        });
    }

    // public function formattedReceivedAmount()
    // {
    //     return number_format($this->receivedAmount(), 2);
    // }
}
