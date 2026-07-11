<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->table('commerce_branches', function (Blueprint $table): void {
            if (! Schema::connection($this->connection)->hasColumn('commerce_branches', 'delivery_radius_km')) {
                $table->decimal('delivery_radius_km', 8, 2)->default(5)->after('is_open');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_branches', 'preparation_minutes')) {
                $table->unsignedSmallInteger('preparation_minutes')->default(30)->after('delivery_radius_km');
            }
        });

        Schema::connection($this->connection)->table('commerce_catalog_branch_stocks', function (Blueprint $table): void {
            $schema = Schema::connection($this->connection);
            $columns = [
                'is_assigned' => fn () => $table->boolean('is_assigned')->default(false)->after('variant_id'),
                'branch_price' => fn () => $table->decimal('branch_price', 12, 2)->nullable()->after('minimum_stock'),
                'branch_sale_price' => fn () => $table->decimal('branch_sale_price', 12, 2)->nullable()->after('branch_price'),
                'branch_sale_starts_at' => fn () => $table->dateTime('branch_sale_starts_at')->nullable()->after('branch_sale_price'),
                'branch_sale_ends_at' => fn () => $table->dateTime('branch_sale_ends_at')->nullable()->after('branch_sale_starts_at'),
                'max_purchase_quantity' => fn () => $table->decimal('max_purchase_quantity', 14, 3)->nullable()->after('branch_sale_ends_at'),
                'available_days' => fn () => $table->json('available_days')->nullable()->after('max_purchase_quantity'),
                'available_from' => fn () => $table->time('available_from')->nullable()->after('available_days'),
                'available_to' => fn () => $table->time('available_to')->nullable()->after('available_from'),
                'fulfillment_priority' => fn () => $table->unsignedSmallInteger('fulfillment_priority')->default(100)->after('available_to'),
                'coverage_radius_km' => fn () => $table->decimal('coverage_radius_km', 8, 2)->nullable()->after('fulfillment_priority'),
                'allow_delivery' => fn () => $table->boolean('allow_delivery')->default(true)->after('coverage_radius_km'),
                'allow_pickup' => fn () => $table->boolean('allow_pickup')->default(true)->after('allow_delivery'),
            ];

            foreach ($columns as $column => $definition) {
                if (! $schema->hasColumn('commerce_catalog_branch_stocks', $column)) {
                    $definition();
                }
            }
        });

        DB::connection($this->connection)
            ->table('commerce_catalog_branch_stocks')
            ->where(function ($query): void {
                $query->where('is_available', true)
                    ->orWhere('stock', '>', 0);
            })
            ->update([
                'is_assigned' => true,
                'allow_delivery' => true,
                'allow_pickup' => true,
            ]);
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('commerce_catalog_branch_stocks', function (Blueprint $table): void {
            $schema = Schema::connection($this->connection);

            foreach ([
                'is_assigned',
                'branch_price',
                'branch_sale_price',
                'branch_sale_starts_at',
                'branch_sale_ends_at',
                'max_purchase_quantity',
                'available_days',
                'available_from',
                'available_to',
                'fulfillment_priority',
                'coverage_radius_km',
                'allow_delivery',
                'allow_pickup',
            ] as $column) {
                if ($schema->hasColumn('commerce_catalog_branch_stocks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::connection($this->connection)->table('commerce_branches', function (Blueprint $table): void {
            $schema = Schema::connection($this->connection);

            foreach (['delivery_radius_km', 'preparation_minutes'] as $column) {
                if ($schema->hasColumn('commerce_branches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
