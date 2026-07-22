<?php

use App\Http\Controllers\Comercio\Auth\GoogleController;
use App\Http\Controllers\Comercio\Auth\LoginController;
use App\Http\Controllers\Comercio\Auth\RegisterController;
use App\Http\Controllers\Comercio\DashboardController;
use App\Http\Controllers\Comercio\CommerceContactController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Comercio\CommerceBranchController;

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

    Route::post('/contactos', [CommerceContactController::class, 'store'])->name('contacts.store');
    Route::put('/contactos/{contact}', [CommerceContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contactos/{contact}', [CommerceContactController::class, 'destroy'])->name('contacts.destroy');
    Route::patch('/contactos/{contact}/principal', [CommerceContactController::class, 'setPrimary'])->name('contacts.primary');

    Route::post('/sucursales', [CommerceBranchController::class, 'store'])->name('branches.store');
    Route::get('/sucursales/{branch}', fn () => redirect()->route('comercio.dashboard', ['tab' => 'sucursales']))->name('branches.show.redirect');
    Route::post('/sucursales/{branch}', [CommerceBranchController::class, 'update'])->name('branches.update.post');
    Route::put('/sucursales/{branch}', [CommerceBranchController::class, 'update'])->name('branches.update');
    Route::delete('/sucursales/{branch}', [CommerceBranchController::class, 'destroy'])->name('branches.destroy');
    Route::patch('/sucursales/{branch}/servicio', [CommerceBranchController::class, 'toggleService'])->name('branches.service');
});