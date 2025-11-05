<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

// ============== USER AUTHENTICATION ROUTES ==============

// Public routes (no authentication required)
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware(['auth:api'])->prefix('v1/auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ============== ADMIN AUTHENTICATION ROUTES ==============

// Admin public routes (no authentication required)
Route::prefix('v1/admin')->group(function () {
    Route::post('/login', [AuthController::class, 'adminLogin']);
});

// Admin protected routes (admin authentication required)
Route::middleware(['auth:admin-api'])->prefix('v1/admin')->group(function () {
    Route::get('/profile', [AuthController::class, 'adminProfile']);
    Route::put('/profile', [AuthController::class, 'updateAdminProfile']);
    Route::post('/logout', [AuthController::class, 'adminLogout']);
});
