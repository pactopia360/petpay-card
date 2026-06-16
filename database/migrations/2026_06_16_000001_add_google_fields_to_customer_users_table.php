<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_cliente';

    public function up(): void
    {
        Schema::connection($this->connection)->table('customer_users', function (Blueprint $table) {
            if (! Schema::connection($this->connection)->hasColumn('customer_users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }

            if (! Schema::connection($this->connection)->hasColumn('customer_users', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }

            if (! Schema::connection($this->connection)->hasColumn('customer_users', 'auth_provider')) {
                $table->string('auth_provider')->default('email')->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('customer_users', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('customer_users', 'auth_provider')) {
                $table->dropColumn('auth_provider');
            }

            if (Schema::connection($this->connection)->hasColumn('customer_users', 'avatar')) {
                $table->dropColumn('avatar');
            }

            if (Schema::connection($this->connection)->hasColumn('customer_users', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }
        });
    }
};