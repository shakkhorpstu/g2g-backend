<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ============== CORE AUTHENTICATION ROUTES ==============
// Authentication is infrastructure-level, not module-specific

// Public routes (no authentication required)
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware(['auth:api'])->prefix('v1/auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// ============== ADMIN AUTHENTICATION ROUTES ==============

// Admin public routes (no authentication required)
Route::prefix('v1/admin')->group(function () {
    Route::post('/login', [AuthController::class, 'adminLogin']);
});

// Admin protected routes (admin authentication required)
Route::middleware(['auth:admin-api'])->prefix('v1/admin')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// ============== GLOBAL API ROUTES ==============
// This file is for global/core API routes only
// Module-specific routes should be defined in their respective modules

// Example: Test routes for error handling (can be removed in production)
Route::prefix('api/v1/test')->group(function () {
    // Test 404 error
    Route::get('/404', function () {
        abort(404);
    });
    
    // Test 500 error
    Route::get('/500', function () {
        throw new \Exception('Test internal server error');
    });
    
    // Test validation error
    Route::post('/validation', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);
        
        return response()->json(['message' => 'Validation passed']);
    });
    
    // Test service exception
    Route::get('/service-exception', function () {
        throw new \App\Exceptions\ServiceException('Test service exception', 400, null, ['field' => ['Test validation error']]);
    });
    
    // Test unauthorized error
    Route::get('/unauthorized', function () {
        throw new \Illuminate\Auth\Access\AuthorizationException('Test unauthorized access');
    });
});