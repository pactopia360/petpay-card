<?php

$path = __DIR__.'/../resources/views/comercio/dashboard.blade.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer dashboard.blade.php\n");
    exit(1);
}

$pattern = '~<button\s+type="button"\s+class="commerce-admin__tab"\s+disabled>\s*Catálogos\s*</button>~u';

$replacement = <<<'BLADE'
<button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'catalogos' ? 'is-active' : '' }}"
                data-commerce-tab-button="catalogos"
                aria-pressed="{{ ($activeTab ?? 'usuarios') === 'catalogos' ? 'true' : 'false' }}"
            >
                Catálogos
            </button>
BLADE;

$content = preg_replace($pattern, $replacement, $content, 1, $count);

if ($count !== 1) {
    fwrite(STDERR, "ERROR: No se encontro exactamente un boton Catalogos deshabilitado.\n");
    exit(1);
}

if (strpos($content, 'data-commerce-tab-panel="catalogos"') === false) {
    fwrite(STDERR, "ERROR: El panel Catalogos no existe en dashboard.blade.php.\n");
    exit(1);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar dashboard.blade.php\n");
    exit(1);
}

echo "OK: Boton Catalogos habilitado.\n";