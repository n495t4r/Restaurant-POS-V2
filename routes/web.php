<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\StockManagementController;
use App\Models\Recipe;
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



// Route::redirect('/', '/peva');
// Route::get('/recipes/{recipe}', function (Recipe $recipe) {
//     // Render the Filament resource view for the specified recipe
//     return view('filament.resources.recipes.view', ['recipe' => $recipe]);
// })->name('filament.resources.recipes.view');


Route::get('/', function () {
    return view('welcome');
});



Route::prefix('/peva')->middleware('auth')->group(function () {
    Route::get('print/{id}', [PrintController::class, 'printInvoice'])->name('print.invoice');

    // Route::get('/stock', [StockManagementController::class, 'index'])->name('stock.index');

    // Route::view('/', 'welcome')->name('home');
    // Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    // Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    // Route::delete('/cart/delete', [CartController::class, 'delete']);
    // Route::delete('/cart/empty', [CartController::class, 'empty']);

});