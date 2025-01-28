<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'ip_address',
        'user_agent',
        'response_code'
    ];

    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }
}
