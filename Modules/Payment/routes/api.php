<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\ClientCardController;
use Modules\Payment\Http\Controllers\PswCardController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Card
    Route::group(['prefix' => 'cards'], function() {
        Route::get('/', [ClientCardController::class, 'index']);
        Route::post('/', [ClientCardController::class, 'store']);
        Route::get('/{payment_method_id}', [ClientCardController::class, 'show']);
        Route::put('/{payment_method_id}', [ClientCardController::class, 'update']);
        Route::delete('/{payment_method_id}', [ClientCardController::class, 'destroy']);

        // Card Transactions
        Route::get('/{payment_method_id}/transactions', [\Modules\Payment\Http\Controllers\ClientCardTransactionController::class, 'index']);
        Route::get('/{payment_method_id}/transactions/{transaction_id}', [\Modules\Payment\Http\Controllers\ClientCardTransactionController::class, 'show']);
        Route::post('/{payment_method_id}/transactions', [\Modules\Payment\Http\Controllers\ClientCardTransactionController::class, 'store']);
    });
});

// ============== PSW ROUTES ==============
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    // Card
    Route::group(['prefix' => 'cards'], function() {
        Route::get('/', [PswCardController::class, 'index']);
        Route::post('/', [PswCardController::class, 'store']);
        Route::get('/{payment_method_id}', [PswCardController::class, 'show']);
        Route::put('/{payment_method_id}', [PswCardController::class, 'update']);
        Route::delete('/{payment_method_id}', [PswCardController::class, 'destroy']);

        // Card Transactions
        Route::get('/{payment_method_id}/transactions', [\Modules\Payment\Http\Controllers\PswCardTransactionController::class, 'index']);
        Route::get('/{payment_method_id}/transactions/{transaction_id}', [\Modules\Payment\Http\Controllers\PswCardTransactionController::class, 'show']);
        Route::post('/{payment_method_id}/transactions', [\Modules\Payment\Http\Controllers\PswCardTransactionController::class, 'store']);
    });
});