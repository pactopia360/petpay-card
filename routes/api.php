<?php

use App\Http\Controllers\Api\Public\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/public/health', HealthController::class)->name('api.public.health');