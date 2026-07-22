<?php

function replace_once(string $content, string $search, string $replace, string $label): string
{
    if (str_contains($content, $replace)) {
        return $content;
    }

    $position = strpos($content, $search);

    if ($position === false) {
        fwrite(STDERR, "ERROR: no se encontro {$label}\n");
        exit(1);
    }

    return substr_replace($content, $replace, $position, strlen($search));
}

$dashboardPath = __DIR__.'/../resources/views/comercio/dashboard.blade.php';
$dashboard = file_get_contents($dashboardPath);

$financeButtonOld = <<<'BLADE'
            <button type="button" class="commerce-admin__tab" disabled>
                Finanzas
            </button>
BLADE;

$financeButtonNew = <<<'BLADE'
            <button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'finanzas' ? 'is-active' : '' }}"
                data-commerce-tab-button="finanzas"
                aria-pressed="{{ ($activeTab ?? 'usuarios') === 'finanzas' ? 'true' : 'false' }}"
            >
                Finanzas
            </button>
BLADE;

$dashboard = replace_once($dashboard, $financeButtonOld, $financeButtonNew, 'boton Finanzas');

$catalogCssNeedle = <<<'BLADE'
    <link
        rel="stylesheet"
        href="{{ asset('assets/petpay-card/css/portals/comercio-catalogos.css') }}?v=20260712-02"
    >
BLADE;

$catalogCssReplacement = $catalogCssNeedle.<<<'BLADE'

    <link
        rel="stylesheet"
        href="{{ asset('assets/petpay-card/css/portals/comercio-finanzas.css') }}?v=20260711-01"
    >
BLADE;

$dashboard = replace_once($dashboard, $catalogCssNeedle, $catalogCssReplacement, 'enlace CSS de Catalogos');

if (! str_contains($dashboard, "@include('comercio.partials.finance')")) {
    $brandingPattern = '~<div\s+class="commerce-tab-panel\s+\{\{\s*\(\$activeTab.*?branding.*?data-commerce-tab-panel="branding"~s';

    if (! preg_match($brandingPattern, $dashboard, $match, PREG_OFFSET_CAPTURE)) {
        fwrite(STDERR, "ERROR: no se encontro el panel Branding\n");
        exit(1);
    }

    $offset = $match[0][1];
    $dashboard = substr_replace(
        $dashboard,
        "        @include('comercio.partials.finance')\n\n",
        $offset,
        0
    );
}

$catalogScriptNeedle = <<<'BLADE'
    <script src="{{ asset('assets/petpay-card/js/portals/comercio-catalogos.js') }}?v=20260712-03"></script>
BLADE;

$catalogScriptReplacement = $catalogScriptNeedle.<<<'BLADE'

    <script src="{{ asset('assets/petpay-card/js/portals/comercio-finanzas.js') }}?v=20260711-01"></script>
BLADE;

if (str_contains($dashboard, $catalogScriptNeedle)) {
    $dashboard = replace_once($dashboard, $catalogScriptNeedle, $catalogScriptReplacement, 'script Catalogos');
} elseif (! str_contains($dashboard, 'comercio-finanzas.js')) {
    $lastEndPush = strrpos($dashboard, '@endpush');

    if ($lastEndPush === false) {
        fwrite(STDERR, "ERROR: no se encontro @endpush para scripts\n");
        exit(1);
    }

    $dashboard = substr_replace(
        $dashboard,
        "    <script src=\"{{ asset('assets/petpay-card/js/portals/comercio-finanzas.js') }}?v=20260711-01\"></script>\n",
        $lastEndPush,
        0
    );
}

file_put_contents($dashboardPath, $dashboard);

$controllerPath = __DIR__.'/../app/Http/Controllers/Comercio/DashboardController.php';
$controller = file_get_contents($controllerPath);

$allowedOld = <<<'PHP'
            'catalogos',
        ];
PHP;

$allowedNew = <<<'PHP'
            'catalogos',
            'finanzas',
        ];
PHP;

$controller = replace_once($controller, $allowedOld, $allowedNew, 'allowedTabs');
file_put_contents($controllerPath, $controller);

$routesPath = __DIR__.'/../routes/comercio.php';
$routes = file_get_contents($routesPath);

$useNeedle = "use App\\Http\\Controllers\\Comercio\\CommerceContactController;\n";
$useReplace = $useNeedle."use App\\Http\\Controllers\\Comercio\\CommerceFinanceController;\n";
$routes = replace_once($routes, $useNeedle, $useReplace, 'use CommerceFinanceController');

if (! str_contains($routes, "comercio.finance.data")) {
    $brandingMarker = <<<'PHP'
            Route::post(
                '/branding',
PHP;

    $financeRoutes = <<<'PHP'
            /*
            |--------------------------------------------------------------------------
            | Finanzas
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/finanzas/data',
                [CommerceFinanceController::class, 'data']
            )->name('finance.data');

            Route::get(
                '/finanzas/movimientos/exportar',
                [CommerceFinanceController::class, 'exportMovements']
            )->name('finance.movements.export');

            Route::post(
                '/finanzas/datos-fiscales',
                [CommerceFinanceController::class, 'saveTaxProfile']
            )->name('finance.tax.save');

            Route::post(
                '/finanzas/datos-bancarios',
                [CommerceFinanceController::class, 'saveBankAccount']
            )->name('finance.bank.save');

            Route::post(
                '/finanzas/aclaraciones',
                [CommerceFinanceController::class, 'storeDispute']
            )->name('finance.disputes.store');

PHP;

    $position = strpos($routes, $brandingMarker);

    if ($position === false) {
        fwrite(STDERR, "ERROR: no se encontro marcador de Branding en rutas\n");
        exit(1);
    }

    $routes = substr_replace($routes, $financeRoutes, $position, 0);
}

file_put_contents($routesPath, $routes);

echo "OK: dashboard, controlador y rutas actualizados.\n";