<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'quantity',
        'store',
        'status',
        'product_category_id',
        'counter'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function getCostPerPortionAttribute()
    {
        if (!$this->recipe) {
            return 0;
        }

        $totalCost = $this->recipe->recipeItems->sum(function ($item) {
            return $item->rawMaterial->unit_cost * $item->quantity;
        });

        return $this->recipe->yield > 0 ? $totalCost / $this->recipe->yield : 0;
    }

    public function getRecommendedSellingPriceAttribute()
    {
        return $this->cost_per_portion * 1.3; // 30% markup
    }

    public function storeStock()
    {
        return $this->hasOne(StoreStock::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function newstock(): HasMany
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
    public static function increaseQuantity(int $id, int $quantity, bool $isStore = false): bool
    {
        $product = self::findOrFail($id);  // Use findOrFail to throw an exception if the product is not found

        if ($isStore) {
            $product->store += $quantity;
        } else {
            $product->quantity += $quantity;
        }

        return $product->save();
    }

    /**
     * Decrease the product quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public static function decreaseQuantity(int $id, int $quantity, bool $isStore = false): bool
    {
        $product = self::findOrFail($id);  // Use findOrFail to throw an exception if the product is not found

        if ($isStore) {
            if ($product->store >= $quantity) {
                $product->store -= $quantity;
                return $product->save();
            }
        } else {
            if ($product->quantity >= $quantity) {
                $product->quantity -= $quantity;
                return $product->save();
            }
        }

        return false;
    }

    protected static function booted()
    {
        // Listen for when a product is being deleted
        static::deleting(function ($product) {
            // Delete the image from storage if it's present
            if ($product->image) {
                // Remove the image file from storage
                Storage::disk('public')->delete($product->image);
            }
        });

        // Listen for when a product is being updated (image change)
        static::updated(function ($product) {
            // Check if the image was updated
            if ($product->isDirty('image') && $product->getOriginal('image')) {
                // Delete the old image from storage
                Storage::disk('public')->delete($product->getOriginal('image'));
            }
        });
    }
}
