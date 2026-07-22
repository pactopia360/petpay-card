<?php

$path = __DIR__.'/../resources/views/comercio/dashboard.blade.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer dashboard.blade.php\n");
    exit(1);
}

$buttonPattern = '~<button\b[^>]*data-commerce-tab-button="branding"[^>]*>.*?</button>~s';

$buttonReplacement = <<<'BLADE'
<button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'branding' ? 'is-active' : '' }}"
                data-commerce-tab-button="branding"
                aria-pressed="{{ ($activeTab ?? 'usuarios') === 'branding' ? 'true' : 'false' }}"
            >
                Branding
            </button>
BLADE;

$content = preg_replace(
    $buttonPattern,
    $buttonReplacement,
    $content,
    1,
    $buttonCount
);

if ($buttonCount !== 1) {
    fwrite(STDERR, "ERROR: No se pudo corregir exactamente un boton Branding.\n");
    exit(1);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar dashboard.blade.php\n");
    exit(1);
}

echo "OK: Boton Branding corregido.\n";