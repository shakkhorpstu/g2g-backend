<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;

// Public authentication routes
Route::prefix('v1')->group(function () {
    // Client authentication
    Route::post('client-register', [AuthController::class, 'register']);
    Route::post('client-login', [AuthController::class, 'login']);
    
    // PSW authentication
    Route::post('psw-register', [AuthController::class, 'pswRegister']);
    Route::post('psw-login', [AuthController::class, 'pswLogin']);
    
    // Admin authentication
    Route::post('admin-login', [AuthController::class, 'adminLogin']);
});

// Protected authentication routes - Client
Route::middleware(['auth:api'])->prefix('v1/auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});

// Protected authentication routes - PSW
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    Route::post('logout', [AuthController::class, 'pswLogout']);
    Route::get('profile', [AuthController::class, 'getPswProfile']);
});
