<?php

$path = __DIR__.'/../resources/views/portals/admin/home.blade.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer home.blade.php\n");
    exit(1);
}

$content = str_replace(
    "route('admin.providers.pending')",
    "route('admin.commerces.pending')",
    $content
);

$content = str_replace(
    "\$metrics['providers_pending']",
    "\$metrics['commerces_pending']",
    $content
);

$content = str_replace(
    "\$metrics['providers_approved']",
    "\$metrics['commerces_approved']",
    $content
);

if (strpos($content, "route('admin.branding.pending')") === false) {
    $metric = <<<'BLADE'

                <a href="{{ route('admin.branding.pending') }}" class="petpay-admin-metric">
                    <div class="petpay-admin-metric-top">
                        <div>
                            <strong>{{ number_format($metrics['branding_pending'] ?? 0) }}</strong>
                            <span>Imágenes Branding pendientes</span>
                        </div>

                        <span class="petpay-admin-metric-icon">🖼️</span>
                    </div>
                </a>
BLADE;

    $metricsStart = strpos($content, '<div class="petpay-admin-metrics">');

    if ($metricsStart === false) {
        fwrite(STDERR, "ERROR: No se encontro petpay-admin-metrics.\n");
        exit(1);
    }

    $sectionStart = strpos($content, '<div class="petpay-admin-section">', $metricsStart);

    if ($sectionStart === false) {
        fwrite(STDERR, "ERROR: No se encontro petpay-admin-section despues de metricas.\n");
        exit(1);
    }

    $metricsClose = strrpos(substr($content, 0, $sectionStart), '</div>');

    if ($metricsClose === false || $metricsClose <= $metricsStart) {
        fwrite(STDERR, "ERROR: No se encontro el cierre del bloque de metricas.\n");
        exit(1);
    }

    $content = substr($content, 0, $metricsClose)
        .$metric."\n            "
        .substr($content, $metricsClose);
}

if (substr_count($content, "route('admin.branding.pending')") < 2) {
    $action = <<<'BLADE'
                    <a href="{{ route('admin.branding.pending') }}" class="petpay-admin-action">
                        <div>
                            <span class="petpay-admin-action-icon">🖼️</span>

                            <div style="margin-top: 14px;">
                                <strong>Branding</strong>
                                <span>Revisar imágenes enviadas por los comercios.</span>
                            </div>
                        </div>

                        <span class="petpay-admin-action-arrow">›</span>
                    </a>

BLADE;

    $ordersPattern = '~\s*<a href="#" class="petpay-admin-action">\s*<div>\s*<span class="petpay-admin-action-icon">📦</span>~u';

    if (! preg_match($ordersPattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        fwrite(STDERR, "ERROR: No se encontro la accion Ordenes para insertar Branding.\n");
        exit(1);
    }

    $insertAt = $match[0][1];
    $content = substr($content, 0, $insertAt)
        ."\n".$action
        .substr($content, $insertAt);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar home.blade.php\n");
    exit(1);
}

echo "OK: Dashboard Admin actualizado.\n";