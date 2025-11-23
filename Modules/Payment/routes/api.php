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

    // Google Pay
    Route::post('/pay/google', [\Modules\Payment\Http\Controllers\ClientGooglePayController::class, 'store']);
    Route::post('/pay/google/confirm', [\Modules\Payment\Http\Controllers\ClientGooglePayController::class, 'confirm']);

    // Transactions (Google Pay & Card persisted stripe_transactions)
    Route::group(['prefix' => 'transactions'], function() {
        Route::get('/', [\Modules\Payment\Http\Controllers\ClientTransactionController::class, 'index']);
        Route::get('/{id}', [\Modules\Payment\Http\Controllers\ClientTransactionController::class, 'show']);
        Route::post('/{id}/refund', [\Modules\Payment\Http\Controllers\ClientTransactionController::class, 'refund']);
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

    // Google Pay
    Route::post('/pay/google', [\Modules\Payment\Http\Controllers\PswGooglePayController::class, 'store']);
    Route::post('/pay/google/confirm', [\Modules\Payment\Http\Controllers\PswGooglePayController::class, 'confirm']);

    // Transactions (Google Pay & Card persisted stripe_transactions)
    Route::group(['prefix' => 'transactions'], function() {
        Route::get('/', [\Modules\Payment\Http\Controllers\PswTransactionController::class, 'index']);
        Route::get('/{id}', [\Modules\Payment\Http\Controllers\PswTransactionController::class, 'show']);
        Route::post('/{id}/refund', [\Modules\Payment\Http\Controllers\PswTransactionController::class, 'refund']);
    });
});