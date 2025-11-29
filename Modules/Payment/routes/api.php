<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\Client\CardController;
use Modules\Payment\Http\Controllers\Client\CardTransactionController;
use Modules\Payment\Http\Controllers\Client\ClientCashierWalletController;
use Modules\Payment\Http\Controllers\Client\GooglePayController;
use Modules\Payment\Http\Controllers\Client\TransactionController;
use Modules\Payment\Http\Controllers\Client\ClientCashierCardTransactionController;
use Modules\Payment\Http\Controllers\Client\ClientCashierCardController;
use Modules\Payment\Http\Controllers\PSW\CardController as PswCardController;
use Modules\Payment\Http\Controllers\PSW\CardTransactionController as PswCardTransactionController;
use Modules\Payment\Http\Controllers\PSW\GooglePayController as PswGooglePayController;
use Modules\Payment\Http\Controllers\PSW\TransactionController as PswTransactionController;
use Modules\Payment\Http\Controllers\PSW\PswCashierWalletController;
use Modules\Payment\Http\Controllers\PSW\PswCashierCardTransactionController;
use Modules\Payment\Http\Controllers\PSW\PswCashierCardController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Card
    Route::group(['prefix' => 'cards'], function() {
        Route::get('/', [CardController::class, 'index']);
        Route::post('/', [CardController::class, 'store']);
        Route::get('/{payment_method_id}', [CardController::class, 'show']);
        Route::put('/{payment_method_id}', [CardController::class, 'update']);
        Route::delete('/{payment_method_id}', [CardController::class, 'destroy']);

        // Card Transactions
        Route::get('/{payment_method_id}/transactions', [CardTransactionController::class, 'index']);
        Route::get('/{payment_method_id}/transactions/{transaction_id}', [CardTransactionController::class, 'show']);
        Route::post('/{payment_method_id}/transactions', [CardTransactionController::class, 'store']);

        // Cashier Card Transactions (parallel, do not remove existing)
        Route::post('/{payment_method_id}/cashier/transactions', [ClientCashierCardTransactionController::class, 'store']);
    });

    // Google Pay
    Route::post('/pay/google', [GooglePayController::class, 'store']);
    Route::post('/pay/google/confirm', [GooglePayController::class, 'confirm']);

    // Transactions (Google Pay & Card persisted stripe_transactions)
    Route::group(['prefix' => 'transactions'], function() {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/{id}/refund', [TransactionController::class, 'refund']);
    });

    // Cashier Wallet Payments (Apple Pay / Google Pay via Cashier)
    Route::post('/cashier/wallet/charge', [ClientCashierWalletController::class, 'charge']);
    Route::post('/cashier/wallet/confirm', [ClientCashierWalletController::class, 'confirm']);

    // Cashier Card CRUD (parallel to existing card CRUD)
    Route::group(['prefix' => 'cashier/cards'], function() {
        Route::get('/', [ClientCashierCardController::class, 'index']);
        Route::post('/', [ClientCashierCardController::class, 'store']);
        Route::get('/{payment_method_id}', [ClientCashierCardController::class, 'show']);
        Route::put('/{payment_method_id}', [ClientCashierCardController::class, 'update']);
        Route::delete('/{payment_method_id}', [ClientCashierCardController::class, 'destroy']);
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
        Route::get('/{payment_method_id}/transactions', [PswCardTransactionController::class, 'index']);
        Route::get('/{payment_method_id}/transactions/{transaction_id}', [PswCardTransactionController::class, 'show']);
        Route::post('/{payment_method_id}/transactions', [PswCardTransactionController::class, 'store']);

        // Cashier Card Transactions (parallel, do not remove existing)
        Route::post('/{payment_method_id}/cashier/transactions', [PswCashierCardTransactionController::class, 'store']);
    });

    // Google Pay
    Route::post('/pay/google', [PswGooglePayController::class, 'store']);
    Route::post('/pay/google/confirm', [PswGooglePayController::class, 'confirm']);

    // Transactions (Google Pay & Card persisted stripe_transactions)
    Route::group(['prefix' => 'transactions'], function() {
        Route::get('/', [PswTransactionController::class, 'index']);
        Route::get('/{id}', [PswTransactionController::class, 'show']);
        Route::post('/{id}/refund', [PswTransactionController::class, 'refund']);
    });

    // Cashier Wallet Payments (Apple Pay / Google Pay via Cashier)
    Route::post('/cashier/wallet/charge', [PswCashierWalletController::class, 'charge']);
    Route::post('/cashier/wallet/confirm', [PswCashierWalletController::class, 'confirm']);

    // Cashier Card CRUD (parallel to existing card CRUD)
    Route::group(['prefix' => 'cashier/cards'], function() {
        Route::get('/', [PswCashierCardController::class, 'index']);
        Route::post('/', [PswCashierCardController::class, 'store']);
        Route::get('/{payment_method_id}', [PswCashierCardController::class, 'show']);
        Route::put('/{payment_method_id}', [PswCashierCardController::class, 'update']);
        Route::delete('/{payment_method_id}', [PswCashierCardController::class, 'destroy']);
    });
});
