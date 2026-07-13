<?php

use App\Http\Controllers\Repartidor\Auth\GoogleController;
use App\Http\Controllers\Repartidor\Auth\LoginController;
use App\Http\Controllers\Repartidor\Auth\RegisterController;
use App\Http\Controllers\Repartidor\DashboardController;
use App\Http\Controllers\Repartidor\DriverIdentityController;
use App\Http\Controllers\Repartidor\DriverVehicleController;
use App\Http\Controllers\Repartidor\DriverVehicle3dController;
use App\Http\Controllers\Repartidor\DriverVehiclePhotoController;
use App\Http\Controllers\Repartidor\PhoneVerificationController;
use App\Http\Controllers\Repartidor\DriverDocumentAnalysisController;
use App\Models\Repartidor\DriverUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth('repartidor')->check()
        ? redirect()->route('repartidor.dashboard')
        : redirect()->route('repartidor.login');
})->name('home');

Route::middleware('guest:repartidor')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.store');

    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');

    Route::post('/registro', [RegisterController::class, 'register'])
        ->name('register.store');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
        ->name('google.callback');

    Route::get(
        '/recuperar-password',
        fn () => view('auth.repartidor.forgot-password')
    )->name('password.request');
});

Route::middleware('auth:repartidor')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/registro/pendiente', function () {
        $driver = Auth::guard('repartidor')->user();

        if (! $driver instanceof DriverUser) {
            Auth::guard('repartidor')->logout();

            return redirect()
                ->route('repartidor.login')
                ->withErrors([
                    'email_or_phone' => 'La sesión no es válida. Inicia sesión nuevamente.',
                ]);
        }

        if ($driver->canAccessPortal()) {
            return redirect()->route('repartidor.dashboard');
        }

        return view('auth.repartidor.pending', compact('driver'));
    })->name('registration.pending');

    Route::middleware('role:repartidor,repartidor')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        Route::prefix('identidad')
            ->name('identity.')
            ->controller(DriverIdentityController::class)
            ->group(function (): void {
                Route::post('/perfil', 'saveProfile')
                    ->name('profile.save');

                Route::post('/solicitudes-actualizacion', 'requestUpdate')
                    ->name('update-requests.store');

                Route::post('/direccion', 'saveAddress')
                    ->name('address.save');

                Route::post('/emergencia', 'saveEmergencyContact')
                    ->name('emergency.save');

                Route::post('/referencias', 'saveReferences')
                    ->name('references.save');

                Route::post('/documentos', 'uploadDocument')
                    ->name('documents.upload');

                Route::get('/documentos/{document}', 'document')
                    ->name('documents.show');

                Route::post('/enviar', 'submit')
                    ->name('submit');
            });

        Route::prefix('verificacion-telefonica')
            ->name('phone-verification.')
            ->controller(PhoneVerificationController::class)
            ->group(function (): void {
                Route::post('/solicitar', 'requestCode')
                    ->middleware('throttle:10,1')
                    ->name('request');

                Route::post('/{verification}/confirmar', 'verifyCode')
                    ->middleware('throttle:15,1')
                    ->name('verify');
            });

        Route::post(
            '/vehiculos',
            [DriverVehicleController::class, 'store']
        )->name('vehicles.store');

        Route::put(
            '/vehiculos/{vehicle}',
            [DriverVehicleController::class, 'update']
        )->name('vehicles.update');

        Route::delete(
            '/vehiculos/{vehicle}',
            [DriverVehicleController::class, 'destroy']
        )->name('vehicles.destroy');
        Route::prefix('/vehiculos/{vehicle}/modelo-3d')
            ->name('vehicles.3d.')
            ->controller(DriverVehicle3dController::class)
            ->group(function (): void {
                Route::post('/', 'store')
                    ->name('store');

                Route::post('/{job}/tomas', 'upload')
                    ->name('frames.upload');

                Route::get('/{job}/tomas/{frame}', 'frame')
                    ->name('frames.show');

                Route::delete('/{job}/tomas/{frame}', 'destroyFrame')
                    ->name('frames.destroy');

                Route::post('/{job}/enviar', 'submit')
                    ->name('submit');
            });

        Route::post(
            '/disponibilidad',
            [DriverIdentityController::class, 'updateAvailability']
        )->name('availability.update');
    });
});

Route::middleware([
    'auth:repartidor',
    'role:repartidor,repartidor',
])->get(
    '/vehiculos/{vehicle}/fotografias/{photo}',
    [DriverVehiclePhotoController::class, 'show']
)->name('vehicles.photos.show');


