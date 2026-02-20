<?php

use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Http\Controllers\Parents\GetParentsController;
use App\Http\Controllers\Student\GetStudentController;
use App\Http\Controllers\User\{
    CreateUserController,
    UpdateUserController,
    UserController,
};
use Illuminate\Support\Facades\Route;

// Guest routes
Route::get('reset-password', [ResetPasswordController::class, 'index'])->name('index');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function() {
    // User route
    Route::prefix('user')->name('user.')->group(function() {
        // Get the auth user
        Route::get('/', [UserController::class,'getUser'])->name('get-user');
        Route::post('store', [CreateUserController::class, 'store'])->name('store');
        Route::put('update/{id}', [UpdateUserController::class, 'update'])->name('update');
    });

    // Students route
    Route::prefix('students')
        ->name('students.')
        ->group(function() {
            Route::get('/', [GetStudentController::class, 'get'])->name('get');
        });

    // Parents route
    Route::prefix('parents')
        ->name('parents.')
        ->group(function() {
            Route::get('/', [GetParentsController::class, 'get'])->name('get');
        });
});