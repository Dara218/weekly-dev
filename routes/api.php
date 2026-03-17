<?php

use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Http\Controllers\Parents\GetParentsController;
use App\Http\Controllers\Student\GetStudentController;
use App\Http\Controllers\User\{
    CreateUserController,
    DeleteUserController,
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

        // Create
        Route::controller(CreateUserController::class)->group(function() {
            Route::post('store', 'store')->name('store');
            Route::post('bulk-store', 'bulkStore')->name('bulkStore');
        });

        // Update
        Route::put('update/{id}', [UpdateUserController::class, 'update'])->name('update');

        // Delete
        Route::controller(DeleteUserController::class)->group(function() {
            Route::delete('delete/{id}', 'delete')->name('store');
            Route::delete('bulk-delete', 'bulkDelete')->name('bulkDelete');
        });
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