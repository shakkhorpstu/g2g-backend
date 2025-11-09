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
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::post('client-logout', [UserAuthController::class, 'logout']);
    Route::post('client-refresh', [UserAuthController::class, 'refresh']);
});

// Protected authentication routes - PSW
Route::middleware(['auth:psw-api'])->prefix('v1')->group(function () {
    Route::post('psw-logout', [PswAuthController::class, 'logout']);
    Route::post('psw-refresh', [PswAuthController::class, 'refresh']);
});

// Protected authentication routes - Admin
Route::middleware(['auth:admin-api'])->prefix('v1')->group(function () {
    Route::post('admin-logout', [AdminAuthController::class, 'logout']);
    Route::post('admin-refresh', [AdminAuthController::class, 'refresh']);
});
