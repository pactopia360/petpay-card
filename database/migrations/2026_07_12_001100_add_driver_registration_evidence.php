<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        Schema::connection($this->connection)->table('driver_users', function (Blueprint $table): void {
            $columns = Schema::connection($this->connection)->getColumnListing('driver_users');

            if (! in_array('registration_latitude', $columns, true)) {
                $table->decimal('registration_latitude', 10, 7)->nullable();
            }

            if (! in_array('registration_longitude', $columns, true)) {
                $table->decimal('registration_longitude', 10, 7)->nullable();
            }

            if (! in_array('registration_accuracy_meters', $columns, true)) {
                $table->decimal('registration_accuracy_meters', 10, 2)->nullable();
            }

            if (! in_array('registration_location_source', $columns, true)) {
                $table->string('registration_location_source', 30)->nullable();
            }

            if (! in_array('registration_address_detected', $columns, true)) {
                $table->string('registration_address_detected', 500)->nullable();
            }

            if (! in_array('registration_location_captured_at', $columns, true)) {
                $table->timestamp('registration_location_captured_at')->nullable();
            }

            if (! in_array('registration_ip', $columns, true)) {
                $table->string('registration_ip', 64)->nullable();
            }

            if (! in_array('registration_user_agent', $columns, true)) {
                $table->text('registration_user_agent')->nullable();
            }

            if (! in_array('terms_version', $columns, true)) {
                $table->string('terms_version', 40)->nullable();
            }

            if (! in_array('privacy_version', $columns, true)) {
                $table->string('privacy_version', 40)->nullable();
            }

            if (! in_array('privacy_accepted_at', $columns, true)) {
                $table->timestamp('privacy_accepted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('driver_users', function (Blueprint $table): void {
            foreach ([
                'registration_latitude',
                'registration_longitude',
                'registration_accuracy_meters',
                'registration_location_source',
                'registration_address_detected',
                'registration_location_captured_at',
                'registration_ip',
                'registration_user_agent',
                'terms_version',
                'privacy_version',
                'privacy_accepted_at',
            ] as $column) {
                if (Schema::connection($this->connection)->hasColumn('driver_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
