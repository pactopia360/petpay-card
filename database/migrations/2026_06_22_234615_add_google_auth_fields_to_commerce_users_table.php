<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_users', function (Blueprint $table) {
            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('password');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'google_avatar')) {
                $table->string('google_avatar')->nullable()->after('google_id');
            }

            if (! Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'auth_provider')) {
                $table->string('auth_provider')->default('email')->after('google_avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->table('commerce_users', function (Blueprint $table) {
            if (Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'auth_provider')) {
                $table->dropColumn('auth_provider');
            }

            if (Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'google_avatar')) {
                $table->dropColumn('google_avatar');
            }

            if (Schema::connection('mysql_comercio')->hasColumn('commerce_users', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }
        });
    }
};