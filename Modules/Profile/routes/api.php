<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;
use Modules\Profile\Http\Controllers\UserProfileController;
use Modules\Profile\Http\Controllers\PswProfileController;
use Modules\Profile\Http\Controllers\AdminProfileController;
use Modules\Profile\Http\Controllers\PreferenceController;
use Modules\Profile\Http\Controllers\NotificationController;
use Modules\Profile\Http\Controllers\PasswordController;
use Modules\Profile\Http\Controllers\PswServiceCategoryController;
use Modules\Profile\Http\Controllers\PswAvailabilityController;
use Modules\Profile\Http\Controllers\ProfilePictureController;
use Modules\Profile\Http\Controllers\DocumentTypeController;
use Modules\Profile\Http\Controllers\DocumentController;

// ============== USER/CLIENT ROUTES ==============
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [UserProfileController::class, 'index']);
        Route::put('/', [UserProfileController::class, 'update']);
        Route::post('/verify-contact-change', [UserProfileController::class, 'verifyContactChange']);
    });
    
    // Language preference
    Route::get('language', [ProfileController::class, 'getLanguage']);
    Route::post('language', [ProfileController::class, 'setLanguage']);

    // Notification settings for users
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });

    // Password change for users
    Route::put('change-password', [PasswordController::class, 'changeUserPassword']);

    // Documents
    Route::group(['prefix' => 'documents'], function() {
        Route::get('/types', [DocumentController::class, 'getTypes']);
        Route::post('/', [DocumentController::class, 'storeOrUpdate']);
        Route::get('/', [DocumentController::class, 'userDocument']);
        Route::get('/{document_type_id}', [DocumentController::class, 'show']);
    });
});

// Public endpoints (no auth)
Route::prefix('v1')->group(function () {
    Route::get('preferences', [PreferenceController::class, 'index']);
});

// ============== PSW ROUTES ==============
Route::middleware(['auth:psw-api'])->prefix('v1/psw')->group(function () {
    // Profile
    Route::group(['prefix' => 'profile'], function() {
        Route::get('/', [PswProfileController::class, 'show']);
        Route::put('/', [PswProfileController::class, 'update']); 
        Route::post('/verify-contact-change', [PswProfileController::class, 'verifyContactChange']);
        Route::put('/availability', [PswProfileController::class, 'setAvailability']);
        // Full availability schedule (days + slots)
        Route::get('/availability/schedule', [PswAvailabilityController::class, 'index']);
        Route::put('/availability/schedule', [PswAvailabilityController::class, 'sync']);
        Route::put('/rates', [PswProfileController::class, 'setRates']);
        Route::put('/bio', [PswProfileController::class, 'updateBio']);
        Route::get('/preferences', [PswProfileController::class, 'preferences']);
        Route::put('/preferences', [PswProfileController::class, 'syncPreferences']);
        Route::get('/services', [PswServiceCategoryController::class, 'index']);
        Route::put('/services', [PswServiceCategoryController::class, 'sync']);
    });

    // Language preference
    Route::get('language', [ProfileController::class, 'getLanguage']);
    Route::post('language', [ProfileController::class, 'setLanguage']);
    
    // Notification settings for PSWs
    Route::group(['prefix' => 'notification-config'], function() {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'update']);
    });
    
    // Password change for PSWs
    Route::put('change-password', [PasswordController::class, 'changePswPassword']);

    // Documents
    Route::group(['prefix' => 'documents'], function() {
        Route::get('/types', [DocumentController::class, 'getTypes']);
        Route::post('/', [DocumentController::class, 'storeOrUpdate']);
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/{document_type_id}', [DocumentController::class, 'show']);
    });
});

// ============== ADMIN ROUTES ==============
Route::middleware(['auth:admin-api'])->prefix('v1/admin')->group(function () {
    // Admin password change
    Route::put('change-password', [PasswordController::class, 'changeAdminPassword']);
    
    // Profile management routes
    Route::group(['prefix' => 'profile'], function() { 
        Route::get('/', [AdminProfileController::class, 'show']);
    });

    // Admin preferences management
    Route::group(['prefix' => 'preferences'], function() {
        Route::post('/', [PreferenceController::class, 'store']);
        Route::put('/{id}', [PreferenceController::class, 'update']);
        Route::delete('/{id}', [PreferenceController::class, 'destroy']);
    });

    // Document Types Management (Admin CRUD)
    Route::group(['prefix' => 'document-types'], function() {
        Route::get('/', [DocumentTypeController::class, 'index']);
        Route::get('/{id}', [DocumentTypeController::class, 'show']);
        Route::post('/', [DocumentTypeController::class, 'store']);
        Route::put('/{id}', [DocumentTypeController::class, 'update']);
        Route::delete('/{id}', [DocumentTypeController::class, 'destroy']);
    });
});

// ============== UNIVERSAL PASSWORD CHANGE ROUTE ==============
Route::middleware(['auth:api,psw-api,admin-api'])->prefix('v1')->group(function () {
    Route::put('password/change', [PasswordController::class, 'changePassword']);
});

// ============== PROFILE PICTURE UPLOAD (DYNAMIC ROUTE) ==============
Route::prefix('v1/{user_type}/profile-picture')->group(function () {
    Route::put('/', [ProfilePictureController::class, 'upload'])
        ->middleware('auth:api,psw-api,admin-api')
        ->where('user_type', 'user|psw|admin');
});
