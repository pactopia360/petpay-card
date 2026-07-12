<?php

use App\Http\Controllers\Comercio\Auth\GoogleController;
use App\Http\Controllers\Comercio\Auth\LoginController;
use App\Http\Controllers\Comercio\Auth\RegisterController;
use App\Http\Controllers\Comercio\CommerceBranchController;
use App\Http\Controllers\Comercio\CommerceBrandingController;
use App\Http\Controllers\Comercio\CommerceCatalogController;
use App\Http\Controllers\Comercio\CommerceContactController;
use App\Http\Controllers\Comercio\CommerceContractController;
use App\Http\Controllers\Comercio\CommerceFinanceController;
use App\Http\Controllers\Comercio\CommerceIdentityController;
use App\Http\Controllers\Comercio\CommerceMonetizationController;
use App\Http\Controllers\Comercio\DashboardController;
use App\Http\Middleware\EnsureCommerceIsActive;
use App\Models\Comercio\CommerceUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:comercio')->group(function (): void {
    Route::get('/', fn () => redirect()->route('comercio.login'))->name('home');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/registro', [RegisterController::class, 'register'])->name('register.store');
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
    Route::get('/recuperar-password', fn () => view('auth.comercio.forgot-password'))->name('password.request');
});

Route::middleware('auth:comercio')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/registro/pendiente', function () {
        $commerce = Auth::guard('comercio')->user();

        if (! $commerce instanceof CommerceUser) {
            Auth::guard('comercio')->logout();

            return redirect()
                ->route('comercio.login')
                ->withErrors(['email' => 'La sesión del comercio no es válida. Inicia sesión nuevamente.']);
        }

        if ($commerce->canAccessPortal()) {
            return redirect()->route('comercio.dashboard');
        }

        return view('auth.comercio.pending', compact('commerce'));
    })->name('registration.pending');

    Route::middleware(EnsureCommerceIsActive::class)->group(function (): void {
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

        Route::post('/catalogos/categorias', [CommerceCatalogController::class, 'storeCategory'])->name('catalog.categories.store');
        Route::post('/catalogos/marcas', [CommerceCatalogController::class, 'storeBrand'])->name('catalog.brands.store');
        Route::post('/catalogos/disponibilidad-destino', [CommerceCatalogController::class, 'availabilityPreview'])->name('catalog.availability.preview');
        Route::post('/catalogos/productos', [CommerceCatalogController::class, 'storeProduct'])->name('catalog.products.store');
        Route::put('/catalogos/productos/{product}', [CommerceCatalogController::class, 'updateProduct'])->name('catalog.products.update');
        Route::patch('/catalogos/productos/{product}/visibilidad', [CommerceCatalogController::class, 'toggleVisibility'])->name('catalog.products.visibility');
        Route::delete('/catalogos/productos/{product}', [CommerceCatalogController::class, 'destroyProduct'])->name('catalog.products.destroy');

        Route::get('/finanzas/data', [CommerceFinanceController::class, 'data'])->name('finance.data');
        Route::get('/finanzas/movimientos/exportar', [CommerceFinanceController::class, 'exportMovements'])->name('finance.movements.export');
        Route::post('/finanzas/datos-fiscales', [CommerceFinanceController::class, 'saveTaxProfile'])->name('finance.tax.save');
        Route::post('/finanzas/datos-bancarios', [CommerceFinanceController::class, 'saveBankAccount'])->name('finance.bank.save');
        Route::post('/finanzas/aclaraciones', [CommerceFinanceController::class, 'storeDispute'])->name('finance.disputes.store');
        Route::post('/finanzas/datos-bancarios/{account}/principal', [CommerceFinanceController::class, 'setPrimaryBankAccount'])->name('finance.bank.primary');
        Route::post('/finanzas/datos-bancarios/{account}/estado', [CommerceFinanceController::class, 'toggleBankAccount'])->name('finance.bank.toggle');
        Route::post('/finanzas/series', [CommerceFinanceController::class, 'storeInvoiceSeries'])->name('finance.series.store');

        Route::post('/monetizar/campanas', [CommerceMonetizationController::class, 'store'])->name('monetization.store');
        Route::post('/monetizar/campanas/{campaign}/enviar', [CommerceMonetizationController::class, 'submit'])->name('monetization.submit');
        Route::post('/monetizar/campanas/{campaign}/estado', [CommerceMonetizationController::class, 'toggle'])->name('monetization.toggle');
        Route::delete('/monetizar/campanas/{campaign}', [CommerceMonetizationController::class, 'destroy'])->name('monetization.destroy');

        Route::post('/identidad/perfil', [CommerceIdentityController::class, 'saveProfile'])->name('identity.profile.save');
        Route::post('/identidad/documentos', [CommerceIdentityController::class, 'uploadDocument'])->name('identity.documents.store');
        Route::post('/identidad/enviar', [CommerceIdentityController::class, 'submit'])->name('identity.submit');
        Route::get('/identidad/documentos/{document}', [CommerceIdentityController::class, 'document'])->name('identity.documents.show');

        Route::post('/contratos', [CommerceContractController::class, 'store'])->name('contracts.store');
        Route::get('/contratos/descargar/zip', [CommerceContractController::class, 'downloadZip'])->name('contracts.download.zip');
        Route::post('/contratos/{contract}/documentos', [CommerceContractController::class, 'uploadDocument'])->name('contracts.documents.store');
        Route::post('/contratos/{contract}/enviar', [CommerceContractController::class, 'submit'])->name('contracts.submit');
        Route::post('/contratos/{contract}/firmar', [CommerceContractController::class, 'sign'])->name('contracts.sign');
        Route::get('/contratos/{contract}/evidencia/{type}', [CommerceContractController::class, 'evidence'])->name('contracts.evidence');
        Route::get('/contratos/{contract}/descargar/{type?}', [CommerceContractController::class, 'download'])->name('contracts.download');
        Route::delete('/contratos/{contract}', [CommerceContractController::class, 'destroy'])->name('contracts.destroy');

        Route::post('/branding', [CommerceBrandingController::class, 'update'])->name('branding.update');
        Route::delete('/branding', [CommerceBrandingController::class, 'reset'])->name('branding.reset');
    });
});