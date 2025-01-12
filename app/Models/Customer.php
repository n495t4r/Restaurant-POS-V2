<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'avatar',
        'user_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getAvatarUrl()
    {
        return Storage::url($this->avatar);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public static function getTotalOrderAmount($customerId)
    {
        $today = Carbon::today();

        $total = OrderItem::whereHas('order', function ($query) use ($customerId, $today) {
            $query->where('customer_id', $customerId)
                ->where('channel_id', 6)
                ->whereDate('created_at', $today)
                ->where('status', '!=', 0);
        })->sum('price');

        // If no results found, $total will be null, so we coalesce it to 0
        $total = floatval($total);

        return $total;
    }
    
    public function getCustomerName()
    {
        return $this->name;
    }
    
    public function createdby()
    {
        return $this->user->first_name;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
