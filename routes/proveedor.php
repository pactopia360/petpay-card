<?php

use App\Http\Controllers\Proveedor\Auth\LoginController;
use App\Http\Controllers\Proveedor\Auth\RegisterController;
use App\Http\Controllers\Proveedor\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth('proveedor')->check()
        ? redirect()->route('proveedor.dashboard')
        : redirect()->route('proveedor.login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:proveedor')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:proveedor')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth:proveedor')
    ->name('logout');

Route::get('/registro', function () {
    return view('auth.proveedor.register');
})->middleware('guest:proveedor')->name('register');

Route::post('/registro', [RegisterController::class, 'register'])
    ->middleware('guest:proveedor')
    ->name('register.store');

Route::get('/recuperar-password', function () {
    return view('auth.proveedor.forgot-password');
})->middleware('guest:proveedor')->name('password.request');

Route::middleware(['auth:proveedor', 'role:proveedor,proveedor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});