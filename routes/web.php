<?php

use App\Http\Controllers\Authentication\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(LoginController::class)->group(function () {
    // Guest route
    Route::post('login', 'authenticate')->name('auth.authenticate');

    // Authenticated route
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout')->name('auth.logout');
    });
});
