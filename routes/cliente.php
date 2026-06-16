<?php

use App\Http\Controllers\Cliente\Auth\LoginController;
use App\Http\Controllers\Cliente\Auth\RegisterController;
use App\Http\Controllers\Cliente\HomeController;
use App\Http\Controllers\Cliente\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth('cliente')->check()
        ? redirect()->route('cliente.dashboard')
        : redirect()->route('cliente.login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:cliente')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:cliente')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth:cliente')
    ->name('logout');

Route::get('/registro', function () {
    return view('auth.cliente.register');
})->middleware('guest:cliente')->name('register');

Route::post('/registro', [RegisterController::class, 'register'])
    ->middleware('guest:cliente')
    ->name('register.store');

Route::get('/recuperar-password', function () {
    return view('auth.cliente.forgot-password');
})->middleware('guest:cliente')->name('password.request');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])
    ->middleware('guest:cliente')
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->middleware('guest:cliente')
    ->name('google.callback');

Route::middleware(['auth:cliente', 'role:cliente,cliente'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])
        ->name('dashboard');
});