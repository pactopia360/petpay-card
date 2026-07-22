<?php

function fail(string $message): never
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}

function writeFile(string $path, string $content): void
{
    if (file_put_contents($path, $content) === false) {
        fail("No se pudo guardar {$path}");
    }
}

$root = dirname(__DIR__);

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
$dashboardPath = $root.'/resources/views/comercio/dashboard.blade.php';
$dashboard = file_get_contents($dashboardPath);

if ($dashboard === false) {
    fail('No se pudo leer dashboard.blade.php');
}

/* Habilitar botón Finanzas */
if (! str_contains($dashboard, 'data-commerce-tab-button="finanzas"')) {
    $pattern = '~<button\b(?=[^>]*class="[^"]*commerce-admin__tab[^"]*")(?=[^>]*disabled)[^>]*>\s*Finanzas\s*</button>~s';

    $replacement = <<<'BLADE'
<button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'finanzas' ? 'is-active' : '' }}"
                data-commerce-tab-button="finanzas"
                aria-pressed="{{ ($activeTab ?? 'usuarios') === 'finanzas' ? 'true' : 'false' }}"
            >
                Finanzas
            </button>
BLADE;

    $dashboard = preg_replace($pattern, $replacement, $dashboard, 1, $count);

    if ($count !== 1) {
        fail('No se encontró el botón deshabilitado de Finanzas');
    }
}

/* Agregar CSS sin depender de la versión del archivo de Catálogos */
if (! str_contains($dashboard, 'comercio-finanzas.css')) {
    $styleEnd = strpos($dashboard, '@endpush');

    if ($styleEnd === false) {
        fail('No se encontró el cierre @endpush de estilos');
    }

    $financeCss = <<<'BLADE'

    <link
        rel="stylesheet"
        href="{{ asset('assets/petpay-card/css/portals/comercio-finanzas.css') }}?v=20260711-02"
    >

BLADE;

    $dashboard = substr_replace($dashboard, $financeCss, $styleEnd, 0);
}

/* Agregar panel Finanzas antes del panel Branding */
if (! str_contains($dashboard, "@include('comercio.partials.finance')")) {
    $brandingMarker = 'data-commerce-tab-panel="branding"';
    $brandingPosition = strpos($dashboard, $brandingMarker);

    if ($brandingPosition === false) {
        fail('No se encontró el panel Branding');
    }

    $panelStart = strrpos(substr($dashboard, 0, $brandingPosition), '<div');

    if ($panelStart === false) {
        fail('No se encontró el inicio del panel Branding');
    }

    $include = "        @include('comercio.partials.finance')\n\n";
    $dashboard = substr_replace($dashboard, $include, $panelStart, 0);
}

/* Agregar JS en el último bloque de scripts */
if (! str_contains($dashboard, 'comercio-finanzas.js')) {
    $lastEndPush = strrpos($dashboard, '@endpush');

    if ($lastEndPush === false) {
        fail('No se encontró el cierre @endpush de scripts');
    }

    $financeJs = <<<'BLADE'
    <script src="{{ asset('assets/petpay-card/js/portals/comercio-finanzas.js') }}?v=20260711-02"></script>

BLADE;

    $dashboard = substr_replace($dashboard, $financeJs, $lastEndPush, 0);
}

writeFile($dashboardPath, $dashboard);

/*
|--------------------------------------------------------------------------
| DashboardController
|--------------------------------------------------------------------------
*/
$controllerPath = $root.'/app/Http/Controllers/Comercio/DashboardController.php';
$controller = file_get_contents($controllerPath);

if ($controller === false) {
    fail('No se pudo leer DashboardController.php');
}

if (! preg_match("~'finanzas'~", $controller)) {
    $controller = preg_replace(
        "~('catalogos',\s*)~",
        "$1            'finanzas',\n",
        $controller,
        1,
        $count
    );

    if ($count !== 1) {
        fail('No se pudo agregar Finanzas a allowedTabs');
    }
}

writeFile($controllerPath, $controller);

/*
|--------------------------------------------------------------------------
| Rutas
|--------------------------------------------------------------------------
*/
$routesPath = $root.'/routes/comercio.php';
$routes = file_get_contents($routesPath);

if ($routes === false) {
    fail('No se pudo leer routes/comercio.php');
}

if (! str_contains($routes, 'CommerceFinanceController')) {
    $needle = "use App\\Http\\Controllers\\Comercio\\CommerceContactController;\n";

    if (! str_contains($routes, $needle)) {
        fail('No se encontró el use de CommerceContactController');
    }

    $routes = str_replace(
        $needle,
        $needle."use App\\Http\\Controllers\\Comercio\\CommerceFinanceController;\n",
        $routes
    );
}

if (! str_contains($routes, "->name('finance.data')")) {
    $marker = "            Route::post(\n                '/branding',";

    $position = strpos($routes, $marker);

    if ($position === false) {
        fail('No se encontró el bloque de rutas de Branding');
    }

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

    $routes = substr_replace($routes, $financeRoutes, $position, 0);
}

writeFile($routesPath, $routes);

echo "OK: integración de Finanzas corregida.\n";