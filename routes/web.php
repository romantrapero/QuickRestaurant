<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosScreen;
use App\Livewire\KitchenScreen;

Route::get('/pos', PosScreen::class)->name('pos');
Route::get('/ksd', KitchenScreen::class)->name('kitchen');

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('api')->group(function () {
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show']);
});