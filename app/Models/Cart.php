<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'product_id',
        'qty',
        'price',
        'discount',
        'vat',
        'total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}