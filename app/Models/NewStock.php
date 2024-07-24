<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewStock extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'supply_date',
    ];

    protected $casts = [
        'supply_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
