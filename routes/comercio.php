<?php

use App\Http\Controllers\Comercio\Auth\GoogleController;
use App\Http\Controllers\Comercio\Auth\LoginController;
use App\Http\Controllers\Comercio\Auth\RegisterController;
use App\Http\Controllers\Comercio\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:comercio')->group(function () {
    Route::get('/', fn () => redirect()->route('comercio.login'))->name('home');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');

    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/registro', [RegisterController::class, 'register'])->name('register.store');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

    Route::get('/recuperar-password', function () {
        return view('auth.comercio.forgot-password');
    })->name('password.request');
});

Route::middleware('auth:comercio')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});