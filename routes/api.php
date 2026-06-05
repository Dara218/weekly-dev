<?php

use App\Http\Controllers\AcademicYear\GetAcademicYearController;
use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Http\Controllers\Classes\GetClassesController;
use App\Http\Controllers\Parents\GetParentsController;
use App\Http\Controllers\Section\GetSectionController;
use App\Http\Controllers\Student\{
    DeleteStudentFileController,
    GetStudentController,
    UploadStudentFileController,
};
use App\Http\Controllers\Teacher\GetTeacherController;
use App\Http\Controllers\User\{
    CreateUserController,
    DeleteUserController,
    UpdateUserController,
    UserController,
};
use App\Http\Controllers\User\Document\GetDocumentController;
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

        // File
        Route::prefix('file')->name('file.')->group(function() {
            Route::get('get/{userId}', [GetDocumentController::class, 'get'])->name('get');
        });
    });

    // Students route
    Route::prefix('students')
        ->name('students.')
        ->group(function() {
            Route::get('/', [GetStudentController::class, 'get'])->name('get');
            Route::post('file/upload', [UploadStudentFileController::class, 'upload'])->name('upload');
            Route::delete('file/delete/{fileId}', [DeleteStudentFileController::class, 'delete'])->name('delete');
        });

    // Parents route
    Route::prefix('parents')
        ->name('parents.')
        ->group(function() {
            Route::get('/', [GetParentsController::class, 'get'])->name('get');
        });

    // Teachers route
    Route::prefix('teachers')
        ->name('teachers.')
        ->group(function() {
            Route::get('/', [GetTeacherController::class, 'get'])->name('get');
            Route::put('update/{userId}', [UpdateUserController::class, 'update'])->name('update');
        });

    // Classes route
    Route::prefix('classes')
        ->name('classes.')
        ->group(function() {
            Route::get('/', [GetClassesController::class, 'get'])->name('get');
        });

    // Section route
    Route::prefix('sections')
        ->name('sections.')
        ->group(function() {
            Route::get('/', [GetSectionController::class, 'get'])->name('get');
        });

    // Academic year route
    Route::prefix('academic-years')
        ->name('academic-years.')
        ->group(function() {
            Route::get('/', [GetAcademicYearController::class, 'get'])->name('get');
        });
});