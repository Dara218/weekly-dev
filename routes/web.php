<?php

use App\Http\Controllers\Authentication\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('login', [LoginController::class, 'authenticate'])->name('authenticate');
