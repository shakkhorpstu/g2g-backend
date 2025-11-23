<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\UserAuthController;
use Modules\Core\Http\Controllers\PswAuthController;
use Modules\Core\Http\Controllers\AdminAuthController;
use Modules\Core\Http\Controllers\OtpController;
use Modules\Core\Http\Controllers\AdminServiceCategoryController;
use Modules\Core\Http\Controllers\ServiceCategoryController;

// Public authentication routes
Route::prefix('v1')->group(function () {
    // Client/User authentication
    Route::post('client-register', [UserAuthController::class, 'register']);
    Route::post('client-login', [UserAuthController::class, 'login']);
    Route::post('verify-account', [UserAuthController::class, 'verifyAccount']);
    
    // Forgot & Reset Password
    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [UserAuthController::class, 'resetPassword']);
    
    // PSW authentication
    Route::post('psw-register', [PswAuthController::class, 'register']);
    Route::post('psw-login', [PswAuthController::class, 'login']);
    Route::post('psw-verify-account', [PswAuthController::class, 'verifyAccount']);
    
    // PSW Forgot & Reset Password
    Route::post('psw-forgot-password', [PswAuthController::class, 'forgotPassword']);
    Route::post('psw-reset-password', [PswAuthController::class, 'resetPassword']);
    
    
    // Admin authentication
    Route::post('admin-login', [AdminAuthController::class, 'login']);
    
    // Admin Forgot & Reset Password
    Route::post('admin-forgot-password', [AdminAuthController::class, 'forgotPassword']);
    Route::post('admin-reset-password', [AdminAuthController::class, 'resetPassword']);
    
    // OTP routes
    Route::post('resend-otp', [OtpController::class, 'resendOtp']);
    Route::post('verify-otp', [OtpController::class, 'verifyOtp']);

    // Public service categories (no auth required)
    Route::get('service-categories', [ServiceCategoryController::class, 'list']);
    Route::get('service-categories/{id}', [ServiceCategoryController::class, 'show']);
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

    // Admin service categories CRUD
    Route::prefix('admin/service-categories')->group(function () {
        Route::get('/', [AdminServiceCategoryController::class, 'index']);
        Route::post('/', [AdminServiceCategoryController::class, 'store']);
        Route::get('/{id}', [AdminServiceCategoryController::class, 'show']);
        Route::put('/{id}', [AdminServiceCategoryController::class, 'update']);
        Route::delete('/{id}', [AdminServiceCategoryController::class, 'destroy']);
    });
});
