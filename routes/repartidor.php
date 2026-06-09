<?php

use App\Http\Controllers\Repartidor\Auth\LoginController;
use App\Http\Controllers\Repartidor\Auth\RegisterController;
use App\Http\Controllers\Repartidor\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth('repartidor')->check()
        ? redirect()->route('repartidor.dashboard')
        : redirect()->route('repartidor.login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:repartidor')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:repartidor')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth:repartidor')
    ->name('logout');

Route::get('/registro', function () {
    return view('auth.repartidor.register');
})->middleware('guest:repartidor')->name('register');

Route::post('/registro', [RegisterController::class, 'register'])
    ->middleware('guest:repartidor')
    ->name('register.store');

Route::get('/recuperar-password', function () {
    return view('auth.repartidor.forgot-password');
})->middleware('guest:repartidor')->name('password.request');

Route::middleware(['auth:repartidor', 'role:repartidor,repartidor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});