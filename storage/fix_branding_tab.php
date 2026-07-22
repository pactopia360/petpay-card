<?php

$path = __DIR__.'/../resources/views/comercio/dashboard.blade.php';

if (! is_file($path)) {
    fwrite(STDERR, "ERROR: No existe dashboard.blade.php\n");
    exit(1);
}

$backup = $path.'.bak_branding_tab_'.date('Ymd_His');

if (! copy($path, $backup)) {
    fwrite(STDERR, "ERROR: No se pudo crear el respaldo.\n");
    exit(1);
}

$content = file_get_contents($path);

$old = <<<'BLADE'
            <button type="button" class="commerce-admin__tab" disabled>
                Branding
            </button>
BLADE;

$new = <<<'BLADE'
            <button
                type="button"
                class="commerce-admin__tab {{ ($activeTab ?? 'usuarios') === 'branding' ? 'is-active' : '' }}"
                data-commerce-tab-button="branding"
            >
                Branding
            </button>
BLADE;

if (! str_contains($content, $old)) {
    fwrite(STDERR, "ERROR: No se encontr? el bot?n Branding deshabilitado.\n");
    exit(1);
}

$content = str_replace($old, $new, $content, $count);

if ($count !== 1) {
    fwrite(STDERR, "ERROR: Se encontraron m?ltiples botones Branding.\n");
    exit(1);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar el archivo.\n");
    exit(1);
}

echo "OK: Pesta?a Branding habilitada y activa.\n";
echo "RESPALDO: {$backup}\n";