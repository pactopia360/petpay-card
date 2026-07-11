<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'header_image_path')) {
                $table->string('header_image_path')->nullable()->after('banner_path');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'icon_image_path')) {
                $table->string('icon_image_path')->nullable()->after('header_image_path');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'listing_image_path')) {
                $table->string('listing_image_path')->nullable()->after('icon_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'header_image_path') ? 'header_image_path' : null,
                Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'icon_image_path') ? 'icon_image_path' : null,
                Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', 'listing_image_path') ? 'listing_image_path' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
