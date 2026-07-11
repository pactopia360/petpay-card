<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth('comercio')->check()) {
        return redirect()->route('comercio.dashboard');
    }

    return view('welcome');
})->name('home');
