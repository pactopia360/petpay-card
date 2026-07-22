<?php

$dashboardPath = __DIR__.'/../resources/views/comercio/dashboard.blade.php';
$cssPath = __DIR__.'/../public/assets/petpay-card/css/portals/comercio.css';

$dashboard = file_get_contents($dashboardPath);

if ($dashboard === false) {
    fwrite(STDERR, "ERROR: No se pudo leer dashboard.blade.php\n");
    exit(1);
}

if (! str_contains($dashboard, "@section('content')")) {
    fwrite(STDERR, "ERROR: No se encontro @section('content').\n");
    exit(1);
}

if (! str_contains($dashboard, '@endsection')) {
    $needle = "\n@push('scripts')";

    if (! str_contains($dashboard, $needle)) {
        fwrite(STDERR, "ERROR: No se encontro @push('scripts').\n");
        exit(1);
    }

    $closing = "\n        </div>\n    </section>\n@endsection\n\n@push('scripts')";
    $dashboard = str_replace($needle, $closing, $dashboard, $count);

    if ($count !== 1) {
        fwrite(STDERR, "ERROR: Cierre de seccion ambiguo.\n");
        exit(1);
    }
}

$scriptTag = <<<'BLADE'
    <script src="{{ asset('assets/petpay-card/js/portals/comercio-branches-ui.js') }}?v=20260711-01"></script>
BLADE;

if (! str_contains($dashboard, 'comercio-branches-ui.js')) {
    $lastEndPush = strrpos($dashboard, '@endpush');

    if ($lastEndPush === false) {
        fwrite(STDERR, "ERROR: No se encontro @endpush final.\n");
        exit(1);
    }

    $dashboard = substr($dashboard, 0, $lastEndPush)
        .$scriptTag."\n"
        .substr($dashboard, $lastEndPush);
}

if (file_put_contents($dashboardPath, $dashboard) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar dashboard.blade.php\n");
    exit(1);
}

$css = file_get_contents($cssPath);

if ($css === false) {
    fwrite(STDERR, "ERROR: No se pudo leer comercio.css\n");
    exit(1);
}

$marker = 'PETPAY HEADER TOP AND SERVICE DAYS FINAL 20260711';

if (! str_contains($css, $marker)) {
    $css .= <<<'CSS'

/* ==========================================================================
   PETPAY HEADER TOP AND SERVICE DAYS FINAL 20260711
   ========================================================================== */

.petpay-page-comercio {
    display: block !important;
    min-height: 100vh !important;
}

.petpay-page-comercio > .commerce-black-header {
    position: sticky !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1000 !important;
    display: block !important;
    width: 100vw !important;
    max-width: none !important;
    min-height: 62px !important;
    margin: 0 0 18px calc(50% - 50vw) !important;
    padding: 0 !important;
    background: #050505 !important;
    border: 0 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .18) !important;
    order: -999 !important;
}

.petpay-page-comercio > .commerce-black-header
.commerce-black-header__inner {
    width: min(1120px, calc(100vw - 40px)) !important;
    min-height: 62px !important;
    margin: 0 auto !important;
}

.petpay-page-comercio > .petpay-main {
    display: block !important;
    width: 100% !important;
    order: 0 !important;
}

.commerce-service-day {
    position: relative !important;
    isolation: isolate !important;
    cursor: pointer !important;
    user-select: none !important;
    transition:
        border-color .16s ease,
        background .16s ease,
        box-shadow .16s ease,
        transform .16s ease !important;
}

.commerce-service-day input[type="checkbox"] {
    position: absolute !important;
    inset: 0 !important;
    z-index: 3 !important;
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    opacity: 0 !important;
    cursor: pointer !important;
    appearance: none !important;
}

.commerce-service-day span,
.commerce-service-day small {
    position: relative !important;
    z-index: 2 !important;
    pointer-events: none !important;
}

.commerce-service-day:hover {
    transform: translateY(-1px) !important;
    border-color: rgba(249, 115, 22, .42) !important;
}

.commerce-service-day.is-selected,
.commerce-service-day:has(input[type="checkbox"]:checked) {
    border-color: #f97316 !important;
    background: rgba(249, 115, 22, .16) !important;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, .10) !important;
}

.commerce-service-day.is-selected span,
.commerce-service-day:has(input[type="checkbox"]:checked) span {
    color: #9a3412 !important;
}

.commerce-service-day.is-selected small,
.commerce-service-day:has(input[type="checkbox"]:checked) small {
    color: #b45309 !important;
}

@media (max-width: 640px) {
    .petpay-page-comercio > .commerce-black-header
    .commerce-black-header__inner {
        width: calc(100vw - 20px) !important;
    }
}
CSS;

    if (file_put_contents($cssPath, $css) === false) {
        fwrite(STDERR, "ERROR: No se pudo guardar comercio.css\n");
        exit(1);
    }
}

echo "OK: Header y dias de servicio corregidos.\n";