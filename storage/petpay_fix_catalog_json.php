<?php

$path = __DIR__.'/../resources/views/comercio/dashboard.blade.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "ERROR: No se pudo leer dashboard.blade.php\n");
    exit(1);
}

/*
|--------------------------------------------------------------------------
| 1. Agregar payload PHP seguro dentro del bloque @php del producto
|--------------------------------------------------------------------------
*/
$needle = <<<'BLADE'
                                    $availableStock = collect($product->stocks ?? [])->sum(function ($stock) {
                                        return max(0, (float) $stock->stock - (float) ($stock->reserved_stock ?? 0));
                                    });
BLADE;

$replacement = $needle.<<<'BLADE'


                                    $catalogProductPayload = [
                                        'item_type' => $product->item_type,
                                        'name' => $product->name,
                                        'sku' => $product->sku,
                                        'barcode' => $product->barcode,
                                        'category_id' => $product->category_id,
                                        'brand_id' => $product->brand_id,
                                        'supplier_name' => $product->supplier_name,
                                        'short_description' => $product->short_description,
                                        'description' => $product->description,
                                        'price' => $product->price,
                                        'cost' => $product->cost,
                                        'sale_price' => $product->sale_price,
                                        'sale_starts_at' => optional($product->sale_starts_at)->format('Y-m-d\TH:i'),
                                        'sale_ends_at' => optional($product->sale_ends_at)->format('Y-m-d\TH:i'),
                                        'unit' => $product->unit,
                                        'status' => $product->status,
                                        'track_stock' => (bool) $product->track_stock,
                                        'is_visible' => (bool) $product->is_visible,
                                        'tags' => implode(', ', $product->tags ?? []),
                                        'weight' => $product->weight,
                                        'length' => $product->length,
                                        'width' => $product->width,
                                        'height' => $product->height,
                                        'variants' => $product->variants->map(function ($variant) {
                                            return [
                                                'name' => $variant->name,
                                                'sku' => $variant->sku,
                                                'barcode' => $variant->barcode,
                                                'attributes' => implode(', ', $variant->attributes ?? []),
                                                'price' => $variant->price,
                                                'sale_price' => $variant->sale_price,
                                            ];
                                        })->values()->all(),
                                        'stocks' => $product->stocks->mapWithKeys(function ($stock) {
                                            return [
                                                (string) $stock->branch_id => [
                                                    'stock' => $stock->stock,
                                                    'reserved_stock' => $stock->reserved_stock ?? 0,
                                                    'minimum_stock' => $stock->minimum_stock,
                                                    'is_available' => (bool) $stock->is_available,
                                                ],
                                            ];
                                        })->all(),
                                    ];
BLADE;

if (strpos($content, '$catalogProductPayload = [') === false) {
    if (strpos($content, $needle) === false) {
        fwrite(STDERR, "ERROR: No se encontro el bloque de availableStock.\n");
        exit(1);
    }

    $content = str_replace($needle, $replacement, $content);
}

/*
|--------------------------------------------------------------------------
| 2. Sustituir @json complejo por json_encode escapado
|--------------------------------------------------------------------------
*/
$pattern = <<<'REGEX'
~\s*data-product='@json\(\[\s*"item_type".*?\]\)'~s
REGEX;

$replace = "\n                                            data-product=\"{{ e(json_encode(\$catalogProductPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}\"";

$content = preg_replace($pattern, $replace, $content, 1, $count);

if ($count !== 1 && strpos($content, 'data-product="{{ e(json_encode($catalogProductPayload') === false) {
    fwrite(STDERR, "ERROR: No se pudo sustituir data-product.\n");
    exit(1);
}

if (file_put_contents($path, $content) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar dashboard.blade.php\n");
    exit(1);
}

echo "OK: JSON del producto corregido.\n";