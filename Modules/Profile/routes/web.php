<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;

// Profile module web routes (if needed)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('profiles', ProfileController::class)->names('profile');
});
