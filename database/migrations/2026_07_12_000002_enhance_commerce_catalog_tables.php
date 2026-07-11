<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->table('commerce_catalog_products', function (Blueprint $table): void {
            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'cost')) {
                $table->decimal('cost', 12, 2)->default(0)->after('price');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'sale_starts_at')) {
                $table->dateTime('sale_starts_at')->nullable()->after('sale_price');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'sale_ends_at')) {
                $table->dateTime('sale_ends_at')->nullable()->after('sale_starts_at');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'supplier_name')) {
                $table->string('supplier_name', 160)->nullable()->after('unit');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'tags')) {
                $table->json('tags')->nullable()->after('supplier_name');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'weight')) {
                $table->decimal('weight', 10, 3)->nullable()->after('tags');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'length')) {
                $table->decimal('length', 10, 2)->nullable()->after('weight');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'width')) {
                $table->decimal('width', 10, 2)->nullable()->after('length');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_products', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('width');
            }
        });

        Schema::connection($this->connection)->table('commerce_catalog_branch_stocks', function (Blueprint $table): void {
            if (! Schema::connection($this->connection)->hasColumn('commerce_catalog_branch_stocks', 'reserved_stock')) {
                $table->decimal('reserved_stock', 14, 3)->default(0)->after('stock');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('commerce_catalog_branch_stocks', function (Blueprint $table): void {
            if (Schema::connection($this->connection)->hasColumn('commerce_catalog_branch_stocks', 'reserved_stock')) {
                $table->dropColumn('reserved_stock');
            }
        });

        Schema::connection($this->connection)->table('commerce_catalog_products', function (Blueprint $table): void {
            foreach ([
                'cost',
                'sale_starts_at',
                'sale_ends_at',
                'supplier_name',
                'tags',
                'weight',
                'length',
                'width',
                'height',
            ] as $column) {
                if (Schema::connection($this->connection)->hasColumn('commerce_catalog_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
