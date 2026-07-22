<?php

$path = __DIR__ . '/resources/views/comercio/dashboard.blade.php';

if (! is_file($path)) {
    fwrite(STDERR, "ERROR: No existe dashboard.blade.php\n");
    exit(1);
}

$backup = $path . '.bak_encoding_' . date('Ymd_His');

if (! copy($path, $backup)) {
    fwrite(STDERR, "ERROR: No se pudo crear el respaldo.\n");
    exit(1);
}

$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer el archivo.\n");
    exit(1);
}

$hasMojibake =
    str_contains($content, "\xC3\x83")
    || str_contains($content, "\xC3\x82")
    || str_contains($content, "\xC3\xA2\xE2\x82\xAC");

if (! $hasMojibake) {
    echo "SIN_CAMBIOS: El archivo ya parece estar en UTF-8 correcto.\n";
    echo "RESPALDO: {$backup}\n";
    exit(0);
}

$fixed = iconv('UTF-8', 'Windows-1252//IGNORE', $content);

if ($fixed === false) {
    fwrite(STDERR, "ERROR: No se pudo convertir la codificacion.\n");
    exit(1);
}

if (! mb_check_encoding($fixed, 'UTF-8')) {
    fwrite(STDERR, "ERROR: El resultado no es UTF-8 valido. Se conserva el original.\n");
    exit(1);
}

if (file_put_contents($path, $fixed) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar el archivo corregido.\n");
    exit(1);
}

echo "OK: Codificacion corregida sin modificar estructura ni funciones.\n";
echo "RESPALDO: {$backup}\n";
