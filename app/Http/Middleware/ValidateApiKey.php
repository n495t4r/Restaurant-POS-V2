<?php

// app/Http/Middleware/ValidateApiKey.php
namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // $apiKey = $request->header('X-API-Key');


        // Check header first, then query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        // if (!$apiKey) {
        //     return response()->json(['error' => 'API key is required'], 401);
        // }

        if (!$apiKey) {
            return response()->json(['error' => 'API key is required'], Response::HTTP_UNAUTHORIZED);
        }

        $cacheKey = "api_key:{$apiKey}";
        $key = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($apiKey) {
            return ApiKey::where('key', $apiKey)
                ->where('is_active', true)
                ->first();
        });

        if (!$key) {
            Cache::forget($cacheKey);
            return response()->json(['error' => 'Invalid or inactive API key'], Response::HTTP_UNAUTHORIZED);
        }

        // Check rate limiting
        $rateLimitKey = "rate_limit:{$key->id}:" . now()->format('Y-m-i');
        $requests = Cache::get($rateLimitKey, 0);

        if ($requests >= 60) {
            return response()->json(['error' => 'Rate limit exceeded'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Cache::increment($rateLimitKey);
        // Cache::expire($rateLimitKey, 60);

        Cache::put($rateLimitKey, $requests + 1, now()->addMinutes(1));


        // Update last used timestamp (once per hour to prevent excessive updates)
        Cache::remember("api_key_used:{$key->id}", now()->addHour(), function () use ($key) {
            $key->update(['last_used_at' => now()]);
            return true;
        });

        // Process the request
        $response = $next($request);

        // Log API request
        ApiRequest::create([
            'api_key_id' => $key->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_code' => $response->getStatusCode()
        ]);

        return $response;
    }
}
