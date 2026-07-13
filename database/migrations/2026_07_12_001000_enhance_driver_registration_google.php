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

            if (! in_array('google_id', $columns, true)) {
                $table->string('google_id')->nullable()->unique()->after('password');
            }

            if (! in_array('google_avatar', $columns, true)) {
                $table->string('google_avatar')->nullable()->after('google_id');
            }

            if (! in_array('auth_provider', $columns, true)) {
                $table->string('auth_provider', 30)->default('email')->after('google_avatar');
            }

            if (! in_array('vehicle_make', $columns, true)) {
                $table->string('vehicle_make', 100)->nullable()->after('vehicle_type');
            }

            if (! in_array('vehicle_model', $columns, true)) {
                $table->string('vehicle_model', 100)->nullable()->after('vehicle_make');
            }

            if (! in_array('license_number', $columns, true)) {
                $table->string('license_number', 100)->nullable()->after('vehicle_plate');
            }

            if (! in_array('state', $columns, true)) {
                $table->string('state', 120)->nullable()->after('operation_zone');
            }

            if (! in_array('city', $columns, true)) {
                $table->string('city', 120)->nullable()->after('state');
            }

            if (! in_array('availability_type', $columns, true)) {
                $table->string('availability_type', 50)->nullable()->after('city');
            }

            if (! in_array('whatsapp_enabled', $columns, true)) {
                $table->boolean('whatsapp_enabled')->default(true)->after('availability_type');
            }

            if (! in_array('terms_accepted_at', $columns, true)) {
                $table->timestamp('terms_accepted_at')->nullable()->after('whatsapp_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('driver_users', function (Blueprint $table): void {
            $columns = [
                'google_id',
                'google_avatar',
                'auth_provider',
                'vehicle_make',
                'vehicle_model',
                'license_number',
                'state',
                'city',
                'availability_type',
                'whatsapp_enabled',
                'terms_accepted_at',
            ];

            foreach ($columns as $column) {
                if (Schema::connection($this->connection)->hasColumn('driver_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
