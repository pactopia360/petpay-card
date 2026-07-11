<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'header_branch_id')) {
                $table->unsignedBigInteger('header_branch_id')->nullable()->after('header_image_reviewed_by');
                $table->index('header_branch_id', 'commerce_brandings_header_branch_index');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'icon_branch_id')) {
                $table->unsignedBigInteger('icon_branch_id')->nullable()->after('icon_image_reviewed_by');
                $table->index('icon_branch_id', 'commerce_brandings_icon_branch_index');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'listing_branch_id')) {
                $table->unsignedBigInteger('listing_branch_id')->nullable()->after('listing_image_reviewed_by');
                $table->index('listing_branch_id', 'commerce_brandings_listing_branch_index');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            foreach ([
                'header_branch_id' => 'commerce_brandings_header_branch_index',
                'icon_branch_id' => 'commerce_brandings_icon_branch_index',
                'listing_branch_id' => 'commerce_brandings_listing_branch_index',
            ] as $column => $index) {
                if (Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', $column)) {
                    $table->dropIndex($index);
                    $table->dropColumn($column);
                }
            }
        });
    }
};
