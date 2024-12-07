<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'category',
        'unit_of_measurement',
        'unit_cost',
        'stock_quantity',
        'reorder_level',
        'is_active',
        'user_id'
    ];

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
