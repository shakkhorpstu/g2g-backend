<?php

use Illuminate\Support\Facades\Route;

// This file is for global API routes only
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