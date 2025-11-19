<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;
use Modules\Profile\Http\Controllers\NotificationController;
use Modules\Profile\Http\Controllers\PasswordController;

// ============== USER/CLIENT ROUTES ==============
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/verify-contact-change', [ProfileController::class, 'verifyContactChange']);
    });
    
    // Notification settings for users
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });
    
    // Password change for users
    Route::put('change-password', [PasswordController::class, 'changeUserPassword']);
});

// ============== PSW ROUTES ==============
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [ProfileController::class, 'pswProfile']);
        Route::put('/', [ProfileController::class, 'updatePswProfile']); 
        Route::post('/verify-contact-change', [ProfileController::class, 'verifyPswContactChange']);
    });
    
    // Notification settings for PSWs
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });
    
    // Password change for PSWs
    Route::put('change-password', [PasswordController::class, 'changePswPassword']);
});

// ============== ADMIN ROUTES ==============
Route::middleware(['auth:admin-api'])->prefix('v1/admin')->group(function () {
    // Admin password change
    Route::put('change-password', [PasswordController::class, 'changeAdminPassword']);
    
    // Profile management routes
    Route::group(['prefix' => 'profiles'], function() {
        Route::post('/', [ProfileController::class, 'store']); 
        Route::get('/{userId}', [ProfileController::class, 'show']); 
        Route::put('/{userId}', [ProfileController::class, 'updateById']); 
        Route::delete('/{userId}', [ProfileController::class, 'destroyById']); 
    });
});

// ============== UNIVERSAL PASSWORD CHANGE ROUTE ==============
Route::middleware(['auth:api,psw-api,admin-api'])->prefix('v1')->group(function () {
    Route::put('password/change', [PasswordController::class, 'changePassword']);
});
