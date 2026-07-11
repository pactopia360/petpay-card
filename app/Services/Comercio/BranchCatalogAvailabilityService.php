<?php

namespace App\Services\Comercio;

use App\Models\Comercio\CommerceCatalogBranchStock;
use Illuminate\Support\Collection;

class BranchCatalogAvailabilityService
{
    public function findForDestination(
        int $commerceId,
        float $latitude,
        float $longitude,
        ?int $productId = null,
        float $quantity = 1
    ): array {
        $stocks = CommerceCatalogBranchStock::query()
            ->with([
                'branch:id,commerce_user_id,branch_name,branch_code,latitude,longitude,is_open,delivery_radius_km,preparation_minutes',
                'product:id,commerce_user_id,name,sku,item_type,price,cost,sale_price,sale_starts_at,sale_ends_at,track_stock,is_visible,status',
            ])
            ->where('commerce_user_id', $commerceId)
            ->where('is_assigned', true)
            ->where('is_available', true)
            ->where('allow_delivery', true)
            ->whereNull('variant_id')
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->get()
            ->filter(function (CommerceCatalogBranchStock $stock) use ($quantity): bool {
                $branch = $stock->branch;
                $product = $stock->product;

                if (! $branch || ! $branch->is_open || $branch->latitude === null || $branch->longitude === null) {
                    return false;
                }

                if (! $product || ! $product->is_visible || $product->status !== 'active') {
                    return false;
                }

                if ($stock->max_purchase_quantity !== null && $quantity > (float) $stock->max_purchase_quantity) {
                    return false;
                }

                if ($product->item_type !== 'service' && $product->track_stock && $stock->available_stock < $quantity) {
                    return false;
                }

                return $this->isAvailableNow($stock);
            })
            ->map(function (CommerceCatalogBranchStock $stock) use ($latitude, $longitude): array {
                $branch = $stock->branch;
                $distance = $this->distanceKm(
                    $latitude,
                    $longitude,
                    (float) $branch->latitude,
                    (float) $branch->longitude
                );

                $coverage = (float) ($stock->coverage_radius_km ?: $branch->delivery_radius_km ?: 5);

                return [
                    'product_id' => (int) $stock->product_id,
                    'product_name' => $stock->product->name,
                    'sku' => $stock->product->sku,
                    'branch_id' => (int) $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_code' => $branch->branch_code,
                    'distance_km' => round($distance, 2),
                    'coverage_radius_km' => round($coverage, 2),
                    'inside_coverage' => $distance <= $coverage,
                    'available_stock' => round($stock->available_stock, 3),
                    'price' => round($stock->effective_price, 2),
                    'max_purchase_quantity' => $stock->max_purchase_quantity !== null
                        ? (float) $stock->max_purchase_quantity
                        : null,
                    'preparation_minutes' => (int) ($branch->preparation_minutes ?: 30),
                    'fulfillment_priority' => (int) ($stock->fulfillment_priority ?: 100),
                    'allow_pickup' => (bool) $stock->allow_pickup,
                    'allow_delivery' => (bool) $stock->allow_delivery,
                ];
            })
            ->filter(fn (array $row): bool => $row['inside_coverage'])
            ->sortBy([
                ['distance_km', 'asc'],
                ['fulfillment_priority', 'asc'],
                ['preparation_minutes', 'asc'],
                ['price', 'asc'],
            ])
            ->values();

        return $stocks->all();
    }

    private function isAvailableNow(CommerceCatalogBranchStock $stock): bool
    {
        $days = array_values(array_filter((array) ($stock->available_days ?? [])));

        if ($days !== []) {
            $day = now()->format('N');

            if (! in_array($day, array_map('strval', $days), true)) {
                return false;
            }
        }

        $now = now()->format('H:i');
        $from = $stock->available_from?->format('H:i');
        $to = $stock->available_to?->format('H:i');

        if ($from && $to && ($now < $from || $now > $to)) {
            return false;
        }

        return true;
    }

    private function distanceKm(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
