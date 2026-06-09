<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_cliente';

    public function up(): void
    {
        Schema::connection('mysql_cliente')->create('customer_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->index();

            $table->string('main_address', 255)->nullable();
            $table->decimal('main_latitude', 10, 7)->nullable();
            $table->decimal('main_longitude', 10, 7)->nullable();

            $table->integer('pawpoints_balance')->default(0);
            $table->boolean('is_petpay_plus')->default(false);

            $table->enum('status', [
                'active',
                'pending',
                'blocked',
                'suspended',
                'rejected',
            ])->default('active')->index();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('mysql_cliente')->create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('mysql_cliente')->create('customer_sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::connection('mysql_cliente')->create('customer_pet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_user_id')->index();
            $table->string('name', 120);
            $table->string('species', 80)->nullable();
            $table->string('breed', 120)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('size', 60)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_cliente')->dropIfExists('customer_pet_profiles');
        Schema::connection('mysql_cliente')->dropIfExists('customer_sessions');
        Schema::connection('mysql_cliente')->dropIfExists('customer_password_reset_tokens');
        Schema::connection('mysql_cliente')->dropIfExists('customer_users');
    }
};