<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableController;
use App\Livewire\KitchenScreen;
use App\Livewire\MesaManager;
use App\Livewire\PosScreen;
use Illuminate\Support\Facades\Route;

Route::get('/menu/{table?}', [MenuController::class, 'show'])->name('menu');
Route::get('/pos', PosScreen::class)->name('pos');
Route::get('/ksd', KitchenScreen::class)->name('kitchen');
Route::get('/mesas', MesaManager::class)->name('mesas.manager');
Route::get('/mesas/{table}/qr', [TableController::class, 'downloadQr'])->name('mesas.qr');

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('api')->group(function () {
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/orders/active', [\App\Http\Controllers\OrderController::class, 'active']);
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show']);
    Route::post('/orders/{order}/items', [\App\Http\Controllers\OrderController::class, 'addItems']);
    Route::put('/orders/{order}/items/{orderItem}', [\App\Http\Controllers\OrderController::class, 'updateItem']);
    Route::delete('/orders/{order}/items/{orderItem}', [\App\Http\Controllers\OrderController::class, 'removeItem']);
    Route::post('/orders/{order}/print-ticket', [\App\Http\Controllers\OrderController::class, 'printTicket']);
    Route::post('/orders/{order}/payments', [\App\Http\Controllers\PaymentController::class, 'store']);

    // Cash Shift (Corte de Caja)
    Route::get('/cash-shift/current', [\App\Http\Controllers\CashShiftController::class, 'current']);
    Route::post('/cash-shift/open', [\App\Http\Controllers\CashShiftController::class, 'open']);
    Route::post('/cash-shift/close', [\App\Http\Controllers\CashShiftController::class, 'close']);
});
