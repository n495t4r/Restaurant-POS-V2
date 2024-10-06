<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewStock extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'from',
        'to',
        'user_id',
        'supply_date',
    ];

    protected $casts = [
        'supply_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
