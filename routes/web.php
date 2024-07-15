<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::redirect('/', '/admin');

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/admin')->middleware('auth')->group(function () {
    // Route::view('/', 'welcome')->name('home');
    // Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    // Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    // Route::delete('/cart/delete', [CartController::class, 'delete']);
    // Route::delete('/cart/empty', [CartController::class, 'empty']);

});