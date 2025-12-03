<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\UserAuthController;
use Modules\Core\Http\Controllers\PswAuthController;
use Modules\Core\Http\Controllers\AdminAuthController;
use Modules\Core\Http\Controllers\OtpController;
use Modules\Core\Http\Controllers\AdminServiceCategoryController;
use Modules\Core\Http\Controllers\ServiceCategoryController;
use Modules\Core\Http\Controllers\AddressController;
use Modules\Core\Http\Controllers\ResourceController;
use Modules\Core\Http\Controllers\Client\ClientRegistrationOtpController;
use Modules\Core\Http\Controllers\PSW\PswRegistrationOtpController;

// Public authentication routes
Route::prefix('v1')->group(function () {
    // Client/User authentication
    Route::post('client-register', [UserAuthController::class, 'register']);
    Route::post('client-register/send-otp', [ClientRegistrationOtpController::class, 'sendOtp']);
    Route::post('client-register/verify-account', [UserAuthController::class, 'verifyAccount']);
    Route::post('client-login', [UserAuthController::class, 'login']);
    Route::post('verify-account', [UserAuthController::class, 'verifyAccount']);

    // Route::post('registration/verify-otp', [ClientRegistrationOtpController::class, 'verifyOtp']);
    // Forgot & Reset Password
    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [UserAuthController::class, 'resetPassword']);

    // PSW authentication
    Route::post('psw-register', [PswAuthController::class, 'register']);
    Route::post('psw-register/send-otp', [PswRegistrationOtpController::class, 'sendOtp']);
    Route::post('psw-register/verify-account', [PswAuthController::class, 'verifyAccount']);
    Route::post('psw-login', [PswAuthController::class, 'login']);
    // Route::post('psw/registration/verify-otp', [PswRegistrationOtpController::class, 'verifyOtp']);
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
    // Route::post('verify-otp', [OtpController::class, 'verifyOtp']);
    Route::post('verify-otp', [OtpController::class, 'verifyOtp']);
    Route::post('verify-2fa', [AuthController::class, 'verifyTwoFactor']);

    // Public service categories (no auth required)
    Route::get('service-categories', [ServiceCategoryController::class, 'list']);
    Route::get('service-categories/{id}', [ServiceCategoryController::class, 'show']);
    // Languages list from config
    Route::get('languages', [ResourceController::class, 'languages']);
});

// Protected authentication routes - Client/User
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::post('client-logout', [UserAuthController::class, 'logout']);
    Route::post('client-refresh', [UserAuthController::class, 'refresh']);

    // Registration OTP for client
    Route::post('client-register/send-otp', [ClientRegistrationOtpController::class, 'sendOtp']);
    Route::post('client-register/verify-otp', [ClientRegistrationOtpController::class, 'verifyOtp']);

    // User addresses
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/default', [AddressController::class, 'getDefault']);
        Route::put('/postal-code', [AddressController::class, 'updatePostalCode']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
    });
});

// Protected authentication routes - PSW
Route::middleware(['auth:psw-api'])->prefix('v1')->group(function () {
    Route::post('psw-logout', [PswAuthController::class, 'logout']);
    Route::post('psw-refresh', [PswAuthController::class, 'refresh']);

    // Registration OTP for PSW
    Route::post('psw-register/send-otp', [PswRegistrationOtpController::class, 'sendOtp']);
    Route::post('psw-register/verify-otp', [PswRegistrationOtpController::class, 'verifyOtp']);

    // PSW addresses
    Route::prefix('psw/addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/default', [AddressController::class, 'getDefault']);
        Route::put('/postal-code', [AddressController::class, 'updatePostalCode']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
    });
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
