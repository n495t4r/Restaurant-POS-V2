<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChannel extends Model
{
    use HasFactory;

    protected $fillable = ['channel','user_id'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'channel_id');
    }

}
