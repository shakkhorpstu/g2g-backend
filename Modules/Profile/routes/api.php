<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;
use Modules\Profile\Http\Controllers\NotificationController;

// ============== USER/CLIENT ROUTES ==============
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/', [ProfileController::class, 'update']);
    });
    
    // Notification settings for users
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });
});

// ============== PSW ROUTES ==============
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [ProfileController::class, 'pswProfile']);
        Route::put('/', [ProfileController::class, 'updatePswProfile']); 
    });
    
    // Notification settings for PSWs
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });
});

// ============== ADMIN PROFILE MANAGEMENT ROUTES ==============
Route::middleware(['auth:admin-api'])->prefix('v1/admin/profiles')->group(function () {
    Route::post('/', [ProfileController::class, 'store']); 
    Route::get('/{userId}', [ProfileController::class, 'show']); 
    Route::put('/{userId}', [ProfileController::class, 'updateById']); 
    Route::delete('/{userId}', [ProfileController::class, 'destroyById']); 
});
