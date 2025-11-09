<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;

// Public authentication routes
Route::prefix('v1')->group(function () {
    Route::post('client-register', [AuthController::class, 'register']);
    Route::post('client-login', [AuthController::class, 'login']);
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
});

// Protected authentication routes
Route::middleware(['auth:api'])->prefix('v1/auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});
