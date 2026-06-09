<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_proveedor';

    public function up(): void
    {
        Schema::connection('mysql_proveedor')->create('provider_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->index();

            $table->string('business_name', 191);
            $table->string('business_type', 120)->nullable();
            $table->string('business_phone', 30)->nullable();
            $table->string('business_email', 191)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->decimal('business_latitude', 10, 7)->nullable();
            $table->decimal('business_longitude', 10, 7)->nullable();

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending')->index();

            $table->boolean('is_open')->default(false);
            $table->decimal('commission_percent', 6, 2)->default(0);

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

        Schema::connection('mysql_proveedor')->create('provider_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('mysql_proveedor')->create('provider_sessions', function (Blueprint $table) {
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
        Schema::connection('mysql_proveedor')->dropIfExists('provider_sessions');
        Schema::connection('mysql_proveedor')->dropIfExists('provider_password_reset_tokens');
        Schema::connection('mysql_proveedor')->dropIfExists('provider_users');
    }
};