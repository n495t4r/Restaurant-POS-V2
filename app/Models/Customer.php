<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
