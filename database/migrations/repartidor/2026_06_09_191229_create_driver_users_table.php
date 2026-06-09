<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        Schema::connection('mysql_repartidor')->create('driver_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->index();

            $table->string('vehicle_type', 80)->nullable();
            $table->string('vehicle_plate', 40)->nullable();
            $table->string('operation_zone', 120)->nullable();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending')->index();

            $table->boolean('is_available')->default(false);
            $table->decimal('delivery_commission_percent', 6, 2)->default(0);

            $table->enum('status', [
                'active',
                'pending',
                'blocked',
                'suspended',
                'rejected',
            ])->default('pending')->index();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('mysql_repartidor')->create('driver_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('mysql_repartidor')->create('driver_sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_repartidor')->dropIfExists('driver_sessions');
        Schema::connection('mysql_repartidor')->dropIfExists('driver_password_reset_tokens');
        Schema::connection('mysql_repartidor')->dropIfExists('driver_users');
    }
};