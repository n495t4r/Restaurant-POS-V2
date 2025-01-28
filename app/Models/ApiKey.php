<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'key', 'user_id', 'last_used_at', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($apiKey) {
            $apiKey->key = Str::random(64);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function apiRequests()
    {
        return $this->hasMany(ApiRequest::class);
    }
}
