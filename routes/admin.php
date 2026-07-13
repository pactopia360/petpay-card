<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverApprovalController;
use App\Http\Controllers\Admin\DriverIdentityReviewController;
use App\Http\Controllers\Admin\DriverUpdateRequestController;
use App\Http\Controllers\Admin\ProviderApprovalController;
use App\Http\Controllers\Admin\CommerceApprovalController;
use App\Http\Controllers\Admin\CommerceBrandingReviewController;
use App\Http\Controllers\Admin\CommerceIdentityReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth('admin')->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:admin')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:admin')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth:admin')
    ->name('logout');

Route::get('/recuperar-password', function () {
    return view('auth.admin.forgot-password');
})->middleware('guest:admin')->name('password.request');

Route::middleware(['auth:admin', 'role:admin,admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/comercios/pendientes', [CommerceApprovalController::class, 'index'])
    ->name('commerces.pending');

    Route::post('/comercios/{commerceUser}/aprobar', [CommerceApprovalController::class, 'approve'])
        ->name('commerces.approve');

    Route::post('/comercios/{commerceUser}/rechazar', [CommerceApprovalController::class, 'reject'])
        ->name('commerces.reject');

    Route::get('/identidades', [CommerceIdentityReviewController::class, 'index'])
        ->name('identities.index');

    Route::post('/identidades/{profile}/iniciar', [CommerceIdentityReviewController::class, 'startReview'])
        ->name('identities.start');

    Route::post('/identidades/{profile}/documentos/{document}/revisar', [CommerceIdentityReviewController::class, 'reviewDocument'])
        ->name('identities.documents.review');

    Route::get('/identidades/documentos/{document}', [CommerceIdentityReviewController::class, 'document'])
        ->name('identities.documents.show');

    Route::post('/identidades/{profile}/aprobar', [CommerceIdentityReviewController::class, 'approve'])
        ->name('identities.approve');

    Route::post('/identidades/{profile}/correcciones', [CommerceIdentityReviewController::class, 'corrections'])
        ->name('identities.corrections');

    Route::post('/identidades/{profile}/rechazar', [CommerceIdentityReviewController::class, 'reject'])
        ->name('identities.reject');

    Route::get('/branding/pendientes', [CommerceBrandingReviewController::class, 'index'])
        ->name('branding.pending');

    Route::patch('/branding/{branding}/{type}/aprobar', [CommerceBrandingReviewController::class, 'approve'])
        ->name('branding.approve');

    Route::patch('/branding/{branding}/{type}/rechazar', [CommerceBrandingReviewController::class, 'reject'])
        ->name('branding.reject');
    Route::get('/proveedores/pendientes', [ProviderApprovalController::class, 'index'])
        ->name('providers.pending');

    Route::patch('/proveedores/{providerUser}/aprobar', [ProviderApprovalController::class, 'approve'])
        ->name('providers.approve');

    Route::patch('/proveedores/{providerUser}/rechazar', [ProviderApprovalController::class, 'reject'])
        ->name('providers.reject');

    Route::get('/repartidores/pendientes', [DriverApprovalController::class, 'index'])
        ->name('drivers.pending');

    Route::patch('/repartidores/{driverUser}/aprobar', [DriverApprovalController::class, 'approve'])
        ->name('drivers.approve');

    Route::patch('/repartidores/{driverUser}/rechazar', [DriverApprovalController::class, 'reject'])
        ->name('drivers.reject');

    /*
     * Expedientes posteriores a la aprobación inicial de acceso.
     * No modifica driver_users.approval_status.
     */
    Route::get(
        '/repartidores/expedientes',
        [DriverIdentityReviewController::class, 'index']
    )->name('driver-identities.index');

    Route::get(
        '/repartidores/expedientes/{profile}',
        [DriverIdentityReviewController::class, 'show']
    )->name('driver-identities.show');

    Route::post(
        '/repartidores/expedientes/{profile}/iniciar',
        [DriverIdentityReviewController::class, 'startReview']
    )->name('driver-identities.start');

    Route::post(
        '/repartidores/expedientes/{profile}/documentos/{document}/analizar',
        [DriverIdentityReviewController::class, 'analyze']
    )
        ->middleware('throttle:20,1')
        ->name('driver-identities.documents.analyze');

    Route::post(
        '/repartidores/expedientes/{profile}/documentos/{document}/revisar',
        [DriverIdentityReviewController::class, 'reviewDocument']
    )->name('driver-identities.documents.review');

    Route::get(
        '/repartidores/expedientes/{profile}/documentos/{document}',
        [DriverIdentityReviewController::class, 'document']
    )->name('driver-identities.documents.show');

    Route::post(
        '/repartidores/expedientes/{profile}/correcciones',
        [DriverIdentityReviewController::class, 'corrections']
    )->name('driver-identities.corrections');

    Route::post(
        '/repartidores/expedientes/{profile}/aprobar',
        [DriverIdentityReviewController::class, 'approve']
    )->name('driver-identities.approve');

    Route::post(
        '/repartidores/expedientes/{profile}/rechazar',
        [DriverIdentityReviewController::class, 'reject']
    )->name('driver-identities.reject');

    Route::get(
        '/repartidores/solicitudes-actualizacion',
        [DriverUpdateRequestController::class, 'index']
    )->name('driver-update-requests.index');

    Route::post(
        '/repartidores/solicitudes-actualizacion/{updateRequest}/aprobar',
        [DriverUpdateRequestController::class, 'approve']
    )->name('driver-update-requests.approve');

    Route::post(
        '/repartidores/solicitudes-actualizacion/{updateRequest}/rechazar',
        [DriverUpdateRequestController::class, 'reject']
    )->name('driver-update-requests.reject');

    Route::get(
        '/repartidores/solicitudes-actualizacion/{updateRequest}/evidencia',
        [DriverUpdateRequestController::class, 'evidence']
    )->name('driver-update-requests.evidence');
});

