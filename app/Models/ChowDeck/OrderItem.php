<?php

namespace App\Models\Chowdeck;

use App\Services\ChowdeckService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'id',
        'total_price',
        'reference',
        'status',
        'summary',
        'source',
        'class',
        'currency',
        'created_at',
        'updated_at',
        'delivery_price',
        'time_payment_confirmed',
        'time_customer_received_order',
        'actual_delivery_time',
        'customer',
        'items',
        'timeline',
        'customer_address',
        'vendor_address',
        'vendor_information',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'time_payment_confirmed' => 'datetime',
        'time_customer_received_order' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'customer' => 'array',
        'items' => 'array',
        'timeline' => 'array',
        'customer_address' => 'array',
        'vendor_address' => 'array',
        'vendor_information' => 'array',
    ];
    public static function findByReference($reference)
    {  
        $service = app(ChowdeckService::class);
        return $service->getOrder($reference);
    }
    
    public function order () : BelongsTo {
        return $this->belongsTo(ChowDeckOrder::class, 'id');
    }
    // public function newEloquentBuilder($query)
    // {
    //     return new class($query) extends Builder {
    //         public function get($columns = ['*'])
    //         {
    //             $service = app(ChowdeckService::class);
    //             return $service->getOrders();
    //         }

    //         public function find($id, $columns = ['*'])
    //         {
    //             $service = app(ChowdeckService::class);
    //             return $service->getOrder($id);
    //         }
    //     };
    // }
}