<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\AdminClientController;
use Modules\Admin\Http\Controllers\AdminPswController;
use Modules\Admin\Http\Controllers\AdminDocumentController;

Route::middleware(['auth:admin-api'])->prefix('v1/admins')->group(function () {
    // Admin user management
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
    });

    // Client management
    Route::group(['prefix' => 'clients'], function () {
        Route::get('/', [AdminClientController::class, 'index']);
        Route::get('/{id}', [AdminClientController::class, 'show']);
        Route::get('/{id}/documents', [AdminDocumentController::class, 'clientDocuments']);
    });

    // PSW management
    Route::group(['prefix' => 'psws'], function () {
        Route::get('/', [AdminPswController::class, 'index']);
        Route::get('/{id}', [AdminPswController::class, 'show']);
        Route::get('/{id}/documents', [AdminDocumentController::class, 'pswDocuments']);
    });
});
