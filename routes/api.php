<?php

use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Http\Controllers\Student\GetStudentController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('reset-password', [ResetPasswordController::class, 'index'])->name('index');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function() {
    // Get the auth user
    Route::get('user', [UserController::class, 'getUser'])->name('get-user');

    // Students route
    Route::prefix('students')
        ->name('students.')
        ->group(function() {
            Route::get('/', [GetStudentController::class, 'get'])->name('get');
        });
});