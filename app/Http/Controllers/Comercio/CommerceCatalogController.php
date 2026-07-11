<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use App\Models\Comercio\CommerceCatalogBrand;
use App\Models\Comercio\CommerceCatalogBranchStock;
use App\Models\Comercio\CommerceCatalogCategory;
use App\Models\Comercio\CommerceCatalogProduct;
use App\Models\Comercio\CommerceCatalogProductVariant;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Comercio\BranchCatalogAvailabilityService;

class CommerceCatalogController extends Controller
{
    public function storeCategory(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique(CommerceCatalogCategory::class, 'name')
                    ->where(fn ($query) => $query->where('commerce_user_id', $commerce->id)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CommerceCatalogCategory::query()->create([
            'commerce_user_id' => $commerce->id,
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug(
                CommerceCatalogCategory::class,
                (int) $commerce->id,
                $validated['name']
            ),
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->back('Categoría creada correctamente.');
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique(CommerceCatalogBrand::class, 'name')
                    ->where(fn ($query) => $query->where('commerce_user_id', $commerce->id)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CommerceCatalogBrand::query()->create([
            'commerce_user_id' => $commerce->id,
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug(
                CommerceCatalogBrand::class,
                (int) $commerce->id,
                $validated['name']
            ),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->back('Marca creada correctamente.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();
        $validated = $this->validateProduct($request, $commerce);

        DB::connection('mysql_comercio')->transaction(function () use ($request, $commerce, $validated): void {
            $product = CommerceCatalogProduct::query()->create(
                $this->productPayload($request, $commerce, $validated)
            );

            $this->syncVariants($request, $commerce, $product);
            $this->syncStocks($request, $commerce, $product);
        });

        return $this->back('Producto o servicio guardado correctamente.');
    }

    public function updateProduct(
        Request $request,
        CommerceCatalogProduct $product
    ): RedirectResponse {
        $commerce = $this->commerce();
        $this->assertOwned($product->commerce_user_id, $commerce->id);
        $validated = $this->validateProduct($request, $commerce, $product);

        DB::connection('mysql_comercio')->transaction(function () use ($request, $commerce, $validated, $product): void {
            $product->fill(
                $this->productPayload($request, $commerce, $validated, $product)
            )->save();

            $this->syncVariants($request, $commerce, $product);
            $this->syncStocks($request, $commerce, $product);
        });

        return $this->back('Producto o servicio actualizado correctamente.');
    }

    public function destroyProduct(CommerceCatalogProduct $product): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->assertOwned($product->commerce_user_id, $commerce->id);

        DB::connection('mysql_comercio')->transaction(function () use ($product): void {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            CommerceCatalogBranchStock::query()
                ->where('product_id', $product->id)
                ->delete();

            CommerceCatalogProductVariant::query()
                ->where('product_id', $product->id)
                ->delete();

            $product->delete();
        });

        return $this->back('Producto eliminado correctamente.');
    }

    public function toggleVisibility(CommerceCatalogProduct $product): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->assertOwned($product->commerce_user_id, $commerce->id);

        $product->update([
            'is_visible' => ! $product->is_visible,
            'status' => $product->status === 'draft' ? 'active' : $product->status,
        ]);

        return $this->back(
            $product->is_visible
                ? 'Producto visible para clientes.'
                : 'Producto oculto para clientes.'
        );
    }

    public function availabilityPreview(
        Request $request,
        BranchCatalogAvailabilityService $availability
    ): JsonResponse {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'product_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'numeric', 'min:0.001', 'max:999999.999'],
        ]);

        $productId = isset($validated['product_id'])
            ? (int) $validated['product_id']
            : null;

        if ($productId !== null) {
            $owned = CommerceCatalogProduct::query()
                ->whereKey($productId)
                ->where('commerce_user_id', $commerce->id)
                ->exists();

            abort_unless($owned, 404);
        }

        return response()->json([
            'ok' => true,
            'results' => $availability->findForDestination(
                commerceId: (int) $commerce->id,
                latitude: (float) $validated['latitude'],
                longitude: (float) $validated['longitude'],
                productId: $productId,
                quantity: (float) ($validated['quantity'] ?? 1)
            ),
        ]);
    }

    private function validateProduct(
        Request $request,
        CommerceUser $commerce,
        ?CommerceCatalogProduct $product = null
    ): array {
        return $request->validate([
            'item_type' => ['required', Rule::in(['product', 'service'])],
            'name' => ['required', 'string', 'max:180'],
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique(CommerceCatalogProduct::class, 'sku')
                    ->where(fn ($query) => $query->where('commerce_user_id', $commerce->id))
                    ->ignore($product?->id),
            ],
            'barcode' => ['nullable', 'string', 'max:80'],
            'category_id' => ['nullable', 'integer'],
            'brand_id' => ['nullable', 'integer'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after_or_equal:sale_starts_at'],
            'unit' => ['required', 'string', 'max:30'],
            'supplier_name' => ['nullable', 'string', 'max:160'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'length' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'width' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:6144'],
            'track_stock' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],

            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:140'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
            'variants.*.barcode' => ['nullable', 'string', 'max:80'],
            'variants.*.attributes' => ['nullable', 'string', 'max:1000'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],

            'branch_stock' => ['nullable', 'array'],
            'branch_stock.*' => ['nullable', 'numeric', 'min:0'],
            'branch_reserved_stock' => ['nullable', 'array'],
            'branch_reserved_stock.*' => ['nullable', 'numeric', 'min:0'],
            'branch_minimum_stock' => ['nullable', 'array'],
            'branch_minimum_stock.*' => ['nullable', 'numeric', 'min:0'],
            'branch_assigned' => ['nullable', 'array'],
            'branch_price' => ['nullable', 'array'],
            'branch_price.*' => ['nullable', 'numeric', 'min:0'],
            'branch_sale_price' => ['nullable', 'array'],
            'branch_sale_price.*' => ['nullable', 'numeric', 'min:0'],
            'branch_sale_starts_at' => ['nullable', 'array'],
            'branch_sale_starts_at.*' => ['nullable', 'date'],
            'branch_sale_ends_at' => ['nullable', 'array'],
            'branch_sale_ends_at.*' => ['nullable', 'date'],
            'branch_max_purchase_quantity' => ['nullable', 'array'],
            'branch_max_purchase_quantity.*' => ['nullable', 'numeric', 'min:0.001'],
            'branch_available_days' => ['nullable', 'array'],
            'branch_available_days.*' => ['nullable', 'array'],
            'branch_available_from' => ['nullable', 'array'],
            'branch_available_from.*' => ['nullable', 'date_format:H:i'],
            'branch_available_to' => ['nullable', 'array'],
            'branch_available_to.*' => ['nullable', 'date_format:H:i'],
            'branch_fulfillment_priority' => ['nullable', 'array'],
            'branch_fulfillment_priority.*' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'branch_coverage_radius_km' => ['nullable', 'array'],
            'branch_coverage_radius_km.*' => ['nullable', 'numeric', 'min:0.1', 'max:5000'],
            'branch_allow_delivery' => ['nullable', 'array'],
            'branch_allow_pickup' => ['nullable', 'array'],
            'branch_available' => ['nullable', 'array'],
        ]);
    }

    private function productPayload(
        Request $request,
        CommerceUser $commerce,
        array $validated,
        ?CommerceCatalogProduct $product = null
    ): array {
        $categoryId = $this->ownedOptionalId(
            CommerceCatalogCategory::class,
            $validated['category_id'] ?? null,
            (int) $commerce->id,
            'category_id'
        );

        $brandId = $this->ownedOptionalId(
            CommerceCatalogBrand::class,
            $validated['brand_id'] ?? null,
            (int) $commerce->id,
            'brand_id'
        );

        $imagePath = $product?->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store(
                'commerce-catalog/'.$commerce->id.'/products',
                'public'
            );
        }

        $tags = collect(explode(',', (string) ($validated['tags'] ?? '')))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'commerce_user_id' => $commerce->id,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'item_type' => $validated['item_type'],
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug(
                CommerceCatalogProduct::class,
                (int) $commerce->id,
                $validated['name'],
                $product?->id
            ),
            'sku' => trim($validated['sku']),
            'barcode' => $validated['barcode'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'cost' => $validated['cost'] ?? 0,
            'sale_price' => $validated['sale_price'] ?? null,
            'sale_starts_at' => $validated['sale_starts_at'] ?? null,
            'sale_ends_at' => $validated['sale_ends_at'] ?? null,
            'unit' => $validated['unit'],
            'supplier_name' => $validated['supplier_name'] ?? null,
            'tags' => $tags,
            'weight' => $validated['weight'] ?? null,
            'length' => $validated['length'] ?? null,
            'width' => $validated['width'] ?? null,
            'height' => $validated['height'] ?? null,
            'image_path' => $imagePath,
            'track_stock' => $request->boolean('track_stock'),
            'is_visible' => $request->boolean('is_visible'),
            'status' => $validated['status'],
        ];
    }

    private function syncVariants(
        Request $request,
        CommerceUser $commerce,
        CommerceCatalogProduct $product
    ): void {
        $variants = collect($request->input('variants', []))
            ->filter(fn ($variant) => filled(data_get($variant, 'name')) || filled(data_get($variant, 'sku')))
            ->values();

        $keptIds = [];

        foreach ($variants as $variant) {
            $name = trim((string) data_get($variant, 'name'));
            $sku = trim((string) data_get($variant, 'sku'));

            if ($name === '' || $sku === '') {
                throw ValidationException::withMessages([
                    'variants' => 'Cada variante debe tener nombre y SKU.',
                ]);
            }

            $duplicate = CommerceCatalogProductVariant::query()
                ->where('commerce_user_id', $commerce->id)
                ->where('sku', $sku)
                ->where(function ($query) use ($product): void {
                    $query->where('product_id', '!=', $product->id);
                })
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'variants' => "El SKU de variante {$sku} ya existe.",
                ]);
            }

            $attributes = collect(explode(',', (string) data_get($variant, 'attributes')))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->values()
                ->all();

            $model = CommerceCatalogProductVariant::query()->updateOrCreate(
                [
                    'commerce_user_id' => $commerce->id,
                    'product_id' => $product->id,
                    'sku' => $sku,
                ],
                [
                    'name' => $name,
                    'barcode' => data_get($variant, 'barcode'),
                    'attributes' => $attributes,
                    'price' => data_get($variant, 'price'),
                    'sale_price' => data_get($variant, 'sale_price'),
                    'is_active' => true,
                ]
            );

            $keptIds[] = $model->id;
        }

        CommerceCatalogProductVariant::query()
            ->where('commerce_user_id', $commerce->id)
            ->where('product_id', $product->id)
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
    }

    private function syncStocks(
        Request $request,
        CommerceUser $commerce,
        CommerceCatalogProduct $product
    ): void {
        $branches = CommerceBranch::query()
            ->where('commerce_user_id', $commerce->id)
            ->get(['id', 'delivery_radius_km']);

        foreach ($branches as $branch) {
            $branchId = (int) $branch->id;
            $stock = (float) data_get($request->input('branch_stock', []), $branchId, 0);
            $reserved = (float) data_get($request->input('branch_reserved_stock', []), $branchId, 0);
            $assigned = (bool) data_get($request->input('branch_assigned', []), $branchId, false);

            if ($reserved > $stock) {
                throw ValidationException::withMessages([
                    "branch_reserved_stock.{$branchId}" => 'El stock reservado no puede ser mayor al stock total.',
                ]);
            }

            $branchPrice = data_get($request->input('branch_price', []), $branchId);
            $branchSalePrice = data_get($request->input('branch_sale_price', []), $branchId);

            if ($branchPrice !== null && $branchSalePrice !== null && (float) $branchSalePrice > (float) $branchPrice) {
                throw ValidationException::withMessages([
                    "branch_sale_price.{$branchId}" => 'La oferta de sucursal no puede ser mayor al precio de sucursal.',
                ]);
            }

            $saleStartsAt = data_get($request->input('branch_sale_starts_at', []), $branchId);
            $saleEndsAt = data_get($request->input('branch_sale_ends_at', []), $branchId);

            if ($saleStartsAt && $saleEndsAt && strtotime((string) $saleEndsAt) < strtotime((string) $saleStartsAt)) {
                throw ValidationException::withMessages([
                    "branch_sale_ends_at.{$branchId}" => 'El fin de oferta de sucursal debe ser posterior al inicio.',
                ]);
            }

            CommerceCatalogBranchStock::query()->updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'variant_id' => null,
                ],
                [
                    'commerce_user_id' => $commerce->id,
                    'is_assigned' => $assigned,
                    'stock' => $stock,
                    'reserved_stock' => $reserved,
                    'minimum_stock' => (float) data_get($request->input('branch_minimum_stock', []), $branchId, 0),
                    'branch_price' => $branchPrice !== null && $branchPrice !== '' ? (float) $branchPrice : null,
                    'branch_sale_price' => $branchSalePrice !== null && $branchSalePrice !== '' ? (float) $branchSalePrice : null,
                    'branch_sale_starts_at' => $saleStartsAt ?: null,
                    'branch_sale_ends_at' => $saleEndsAt ?: null,
                    'max_purchase_quantity' => data_get($request->input('branch_max_purchase_quantity', []), $branchId) ?: null,
                    'available_days' => array_values(array_filter(
                        (array) data_get($request->input('branch_available_days', []), $branchId, [])
                    )),
                    'available_from' => data_get($request->input('branch_available_from', []), $branchId) ?: null,
                    'available_to' => data_get($request->input('branch_available_to', []), $branchId) ?: null,
                    'fulfillment_priority' => (int) (data_get($request->input('branch_fulfillment_priority', []), $branchId, 100) ?: 100),
                    'coverage_radius_km' => data_get($request->input('branch_coverage_radius_km', []), $branchId)
                        ?: $branch->delivery_radius_km
                        ?: 5,
                    'allow_delivery' => (bool) data_get($request->input('branch_allow_delivery', []), $branchId, false),
                    'allow_pickup' => (bool) data_get($request->input('branch_allow_pickup', []), $branchId, false),
                    'is_available' => (bool) data_get($request->input('branch_available', []), $branchId, false),
                ]
            );
        }
    }

    private function ownedOptionalId(
        string $modelClass,
        mixed $id,
        int $commerceId,
        string $field
    ): ?int {
        if ($id === null || $id === '') {
            return null;
        }

        $exists = $modelClass::query()
            ->whereKey((int) $id)
            ->where('commerce_user_id', $commerceId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                $field => 'El registro seleccionado no pertenece a este comercio.',
            ]);
        }

        return (int) $id;
    }

    private function uniqueSlug(
        string $modelClass,
        int $commerceId,
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()
            ->where('commerce_user_id', $commerceId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function commerce(): CommerceUser
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce instanceof CommerceUser, 401);

        return $commerce;
    }

    private function assertOwned(int $ownerId, int $commerceId): void
    {
        abort_unless($ownerId === $commerceId, 404);
    }

    private function back(string $message): RedirectResponse
    {
        return redirect()
            ->route('comercio.dashboard', ['tab' => 'catalogos'])
            ->with('status', $message);
    }
}
