<?php

use App\Http\Controllers\Authentication\{
    ResetPasswordController,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('reset-password', [ResetPasswordController::class, 'index'])->name('index');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function() {
    // Get the auth user
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});