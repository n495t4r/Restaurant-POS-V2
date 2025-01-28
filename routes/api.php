<?php

use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['api'])->prefix('v1')->group(function () {
    // Public routes (if any)
    // Route::post('auth/api-key', [ApiKeyController::class, 'store']);
    
    // Protected routes
    Route::middleware(['validate.api.key'])->group(function () {
        // Products
        Route::apiResource('products', ProductApiController::class);
        
        // Categories
        // Route::apiResource('categories', ProductCategoryController::class);
        
        // API Key verification route
        Route::get('verify-key', function () {
            return response()->json(['message' => 'Valid API key']);
        });
    });
});