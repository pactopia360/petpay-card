<?php

$path = __DIR__ . '/../resources/views/comercio/dashboard.blade.php';

if (! is_file($path)) {
    fwrite(STDERR, "ERROR: No existe dashboard.blade.php\n");
    exit(1);
}

$backup = $path . '.bak_mojibake_' . date('Ymd_His');

if (! copy($path, $backup)) {
    fwrite(STDERR, "ERROR: No se pudo crear respaldo.\n");
    exit(1);
}

$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer dashboard.blade.php\n");
    exit(1);
}

$replacements = [
    "\xC3\x83\xC2\xA1" => "\xC3\xA1",
    "\xC3\x83\xC2\xA9" => "\xC3\xA9",
    "\xC3\x83\xC2\xAD" => "\xC3\xAD",
    "\xC3\x83\xC2\xB3" => "\xC3\xB3",
    "\xC3\x83\xC2\xBA" => "\xC3\xBA",
    "\xC3\x83\xC2\xB1" => "\xC3\xB1",
    "\xC3\x83\xC2\xBC" => "\xC3\xBC",

    "\xC3\x83\xC2\x81" => "\xC3\x81",
    "\xC3\x83\xC2\x89" => "\xC3\x89",
    "\xC3\x83\xC2\x8D" => "\xC3\x8D",
    "\xC3\x83\xC2\x93" => "\xC3\x93",
    "\xC3\x83\xC2\x9A" => "\xC3\x9A",
    "\xC3\x83\xC2\x91" => "\xC3\x91",
    "\xC3\x83\xC2\x9C" => "\xC3\x9C",

    "\xC3\x82\xC2\xBF" => "\xC2\xBF",
    "\xC3\x82\xC2\xA1" => "\xC2\xA1",
    "\xC3\x82\xC2\xB0" => "\xC2\xB0",

    "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9C" => "\xE2\x80\x93",
    "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D" => "\xE2\x80\x94",
    "\xC3\xA2\xE2\x82\xAC\xC5\x93" => "\xE2\x80\x9C",
    "\xC3\xA2\xE2\x82\xAC\xC2\x9D" => "\xE2\x80\x9D",
    "\xC3\xA2\xE2\x82\xAC\xCB\x9C" => "\xE2\x80\x98",
    "\xC3\xA2\xE2\x82\xAC\xE2\x84\xA2" => "\xE2\x80\x99",
];

$original = $content;

for ($iteration = 0; $iteration < 5; $iteration++) {
    $previous = $content;
    $content = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $content
    );

    if ($content === $previous) {
        break;
    }
}

if (! mb_check_encoding($content, 'UTF-8')) {
    fwrite(STDERR, "ERROR: El resultado no es UTF-8 valido.\n");
    exit(1);
}

if ($content === $original) {
    echo "AVISO: No se detectaron secuencias conocidas para corregir.\n";
    echo "RESPALDO: {$backup}\n";
    exit(0);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar el archivo corregido.\n");
    exit(1);
}

echo "OK: Textos corregidos.\n";
echo "RESPALDO: {$backup}\n";