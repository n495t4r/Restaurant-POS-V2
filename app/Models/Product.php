<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'quantity',
        'status',
        'product_category_id',
        'counter'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function items() : HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function newstock() : HasMany
    {
        return $this->hasMany(NewStock::class);
    }

    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getParentCategoryName()
    {
        if ($this->product_category && $this->product_category->parent) {
            return $this->product_category->parent->name;
        } else {
            return null; // Or handle it as per your requirement
        }
    }

    public function getFirstChildCategoryName()
    {
        if ($this->product_category && $this->product_category->children->isNotEmpty()) {
            return $this->product_category->children->first()->name;
        } else {
            return null; // Or handle it as per your requirement
        }
    }

      /**
     * Increase the product quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public static function increaseQuantity(int $id, int $quantity): bool
    {
        $product = self::find($id);

        if ($product) {
            $product->quantity += $quantity;
            return $product->save();
        }

        return false;
    }

    /**
     * Decrease the product quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public static function decreaseQuantity(int $id, int $quantity): bool
    {
        $product = self::find($id);

        if ($product && $product->quantity >= $quantity) {
            $product->quantity -= $quantity;
            return $product->save();
        }

        return false;
    }

}
