<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\UserAuthController;
use Modules\Core\Http\Controllers\PswAuthController;
use Modules\Core\Http\Controllers\AdminAuthController;

// Public authentication routes
Route::prefix('v1')->group(function () {
    // Client/User authentication
    Route::post('client-register', [UserAuthController::class, 'register']);
    Route::post('client-login', [UserAuthController::class, 'login']);
    
    // PSW authentication
    Route::post('psw-register', [PswAuthController::class, 'register']);
    Route::post('psw-login', [PswAuthController::class, 'login']);
    
    // Admin authentication
    Route::post('admin-login', [AdminAuthController::class, 'login']);
});

// Protected authentication routes - Client/User
Route::middleware(['auth:api'])->prefix('v1/auth')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::post('change-password', [UserAuthController::class, 'changePassword']);
});

// Protected authentication routes - PSW
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    Route::post('logout', [PswAuthController::class, 'logout']);
    Route::post('refresh', [PswAuthController::class, 'refresh']);
});

// Protected authentication routes - Admin
Route::middleware(['auth:admin-api'])->prefix('v1/admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('refresh', [AdminAuthController::class, 'refresh']);
    Route::get('profile', [AdminAuthController::class, 'profile']);
});
