<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    public function items(): HasMany
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
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
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
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return 'Walk-in Customer';
    }

    public static function oweing_customer(){
        return self::whereNotIn('id', self::full_payment())->pluck('customer_id','customer_id');
    }

    public static function orderId_oweing_customer(int $id){
        return self::where('customer_id', $id)
        ->whereNotIn('id', self::full_payment())
        ->pluck('id','id');
    }

    public static function unpaid_amount(int $id)
    {
        $order_amount = OrderItem::whereNotIn('order_id', Order::failed_order())
            ->whereNotIn('order_id', self::staff_order())
            ->whereNotIn('order_id', self::glovo_order())
            ->whereNotIn('order_id', self::chowdeck_order())
            ->whereIn('order_id', self::orderId_oweing_customer($id))
            ->sum('price');

        $partial_payment = Payment::whereNotIn('order_id', Order::failed_order())
            ->whereNotIn('order_id', self::staff_order())
            ->whereNotIn('order_id', self::glovo_order())
            ->whereNotIn('order_id', self::chowdeck_order())
            ->whereIn('order_id', self::partial_payment())
            ->whereIn('order_id', self::orderId_oweing_customer($id))
            ->sum('paid');
        return $order_amount - $partial_payment;
    
    }

    public static function failed_order($date = null){
        if($date){
            return self::where('status', 0)
            ->whereDate('created_at', $date)->pluck('id','id');    
        }
        
        return self::where('status', 0)->pluck('id','id');
    }

    public static function staff_order($date = null){
        if($date){
            return self::where('channel_id', 6)
            ->whereDate('created_at', $date)->pluck('id','id');    
        }
        
        return self::where('channel_id', 6)->pluck('id','id');
    }

    public static function order_date($date = null){
        if($date){
            return self::whereDate('created_at', $date)->pluck('id','id');    
        }
        
        return self::whereDate('created_at', today())->pluck('id','id');
    }

    public static function glovo_order($date = null){
        if($date){
            return self::where('channel_id', 1)
            ->whereDate('created_at', $date)->pluck('id','id');    
        }
        
        return self::where('channel_id', 1)->pluck('id','id');
    }

    public static function chowdeck_order($date = null){
        if($date){
            return self::where('channel_id', 3)
            ->whereDate('created_at', $date)->pluck('id','id');    
        }
        
        return self::where('channel_id', 3)->pluck('id','id');
    }

    public static function partial_payment()
    {
        $itemTotals = DB::table('order_items')
            ->select('order_id', DB::raw('SUM(price) as total_price'))
            ->groupBy('order_id');

        $paymentTotals = DB::table('payments')
            ->select('order_id', DB::raw('SUM(paid) as total_paid'))
            ->groupBy('order_id');

        $ids = Order::select('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->joinSub($itemTotals, 'item_totals', function ($join) {
                $join->on('orders.id', '=', 'item_totals.order_id');
            })
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('orders.id', '=', 'payment_totals.order_id');
            })
            ->groupBy('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->havingRaw('(total_price - total_paid) < total_price and (total_price - total_paid) > 0')
            ->pluck('orders.id', 'id');

        return $ids;
    }

    public static function full_payment()
    {
        $itemTotals = DB::table('order_items')
            ->select('order_id', DB::raw('SUM(price) as total_price'))
            ->groupBy('order_id');

        $paymentTotals = DB::table('payments')
            ->select('order_id', DB::raw('SUM(paid) as total_paid'))
            ->groupBy('order_id');

        $ids = Order::select('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->joinSub($itemTotals, 'item_totals', function ($join) {
                $join->on('orders.id', '=', 'item_totals.order_id');
            })
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('orders.id', '=', 'payment_totals.order_id');
            })
            ->groupBy('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->havingRaw('(total_price - total_paid) <= 0')
            ->pluck('orders.id', 'id');

        return $ids;
    }

    public static function no_payment()
    {
        $itemTotals = DB::table('order_items')
            ->select('order_id', DB::raw('SUM(price) as total_price'))
            ->groupBy('order_id');

        $paymentTotals = DB::table('payments')
            ->select('order_id', DB::raw('SUM(paid) as total_paid'))
            ->groupBy('order_id');

        $ids = Order::select('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->joinSub($itemTotals, 'item_totals', function ($join) {
                $join->on('orders.id', '=', 'item_totals.order_id');
            })
            ->leftJoinSub($paymentTotals, 'payment_totals', function ($join) {
                $join->on('orders.id', '=', 'payment_totals.order_id');
            })
            ->groupBy('orders.id', 'item_totals.total_price', 'payment_totals.total_paid')
            ->havingRaw('(total_paid = 0 or total_paid is null) and total_price > 0')
            ->pluck('orders.id', 'id');

        return $ids;
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
        return $this->payments->map(function ($i) {
            return json_decode($i->payment_methods);
        });
    }

    // public function formattedReceivedAmount()
    // {
    //     return number_format($this->receivedAmount(), 2);
    // }
}
