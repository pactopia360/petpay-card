<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            foreach (['header', 'icon', 'listing'] as $prefix) {
                if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', "{$prefix}_image_status")) {
                    $table->string("{$prefix}_image_status", 24)->nullable()->after("{$prefix}_image_path");
                }

                if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', "{$prefix}_image_submitted_at")) {
                    $table->timestamp("{$prefix}_image_submitted_at")->nullable()->after("{$prefix}_image_status");
                }

                if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', "{$prefix}_image_reviewed_at")) {
                    $table->timestamp("{$prefix}_image_reviewed_at")->nullable()->after("{$prefix}_image_submitted_at");
                }

                if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', "{$prefix}_image_rejection_reason")) {
                    $table->text("{$prefix}_image_rejection_reason")->nullable()->after("{$prefix}_image_reviewed_at");
                }

                if (! Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', "{$prefix}_image_reviewed_by")) {
                    $table->unsignedBigInteger("{$prefix}_image_reviewed_by")->nullable()->after("{$prefix}_image_rejection_reason");
                }
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_brandings', function (Blueprint $table): void {
            $columns = [];

            foreach (['header', 'icon', 'listing'] as $prefix) {
                foreach ([
                    "{$prefix}_image_status",
                    "{$prefix}_image_submitted_at",
                    "{$prefix}_image_reviewed_at",
                    "{$prefix}_image_rejection_reason",
                    "{$prefix}_image_reviewed_by",
                ] as $column) {
                    if (Schema::connection('mysql_comercio')->hasColumn('commerce_brandings', $column)) {
                        $columns[] = $column;
                    }
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
