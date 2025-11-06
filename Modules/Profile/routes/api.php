<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;

// ============== USER PROFILE ROUTES ==============

// Protected user profile routes (authentication required)
Route::middleware(['auth:api'])->prefix('v1/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'index']);          // Get current user profile
    Route::put('/', [ProfileController::class, 'update']);         // Update current user profile
    Route::delete('/', [ProfileController::class, 'destroy']);     // Delete current user profile
});

// ============== ADMIN PROFILE MANAGEMENT ROUTES ==============

// Admin profile management routes (admin authentication required)
Route::middleware(['auth:admin-api'])->prefix('v1/admin/profiles')->group(function () {
    Route::post('/', [ProfileController::class, 'store']);                    // Create user profile
    Route::get('/{userId}', [ProfileController::class, 'show']);              // Get user profile by ID
    Route::put('/{userId}', [ProfileController::class, 'updateById']);        // Update user profile by ID
    Route::delete('/{userId}', [ProfileController::class, 'destroyById']);    // Delete user profile by ID
});
