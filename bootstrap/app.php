<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->prefix('cliente')
                ->name('cliente.')
                ->group(base_path('routes/cliente.php'));

            Route::middleware('web')
                ->prefix('proveedor')
                ->name('proveedor.')
                ->group(base_path('routes/proveedor.php'));

            Route::middleware('web')
                ->prefix('comercio')
                ->name('comercio.')
                ->group(base_path('routes/comercio.php'));

            Route::middleware('web')
                ->prefix('repartidor')
                ->name('repartidor.')
                ->group(base_path('routes/repartidor.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request): string {
            if (
                $request->is('comercio') ||
                $request->is('comercio/*')
            ) {
                return route('comercio.login');
            }

            if (
                $request->is('cliente') ||
                $request->is('cliente/*')
            ) {
                return route('cliente.login');
            }

            if (
                $request->is('proveedor') ||
                $request->is('proveedor/*')
            ) {
                return route('proveedor.login');
            }

            if (
                $request->is('repartidor') ||
                $request->is('repartidor/*')
            ) {
                return route('repartidor.login');
            }

            return route('admin.login');
        });

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
