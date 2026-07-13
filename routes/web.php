<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }

    if (auth('comercio')->check()) {
        return redirect()->route('comercio.dashboard');
    }

    if (auth('cliente')->check()) {
        return redirect()->route('cliente.dashboard');
    }

    if (auth('repartidor')->check()) {
        return redirect()->route('repartidor.dashboard');
    }

    return view('welcome');
})->name('home');