<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->index();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->enum('status', [
                'active',
                'pending',
                'blocked',
                'suspended',
                'rejected',
            ])->default('pending')->index();

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'user_id']);
        });

        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('position', 120)->nullable();
            $table->string('department', 120)->nullable();
            $table->boolean('can_manage_system')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('main_address', 255)->nullable();
            $table->decimal('main_latitude', 10, 7)->nullable();
            $table->decimal('main_longitude', 10, 7)->nullable();
            $table->integer('pawpoints_balance')->default(0);
            $table->boolean('is_petpay_plus')->default(false);
            $table->timestamps();
        });

        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

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
            $table->timestamps();
        });

        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

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
            $table->timestamps();
        });

        Schema::create('customer_pet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('species', 80)->nullable();
            $table->string('breed', 120)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('size', 60)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->foreignId('user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('customer_pet_profiles');
        Schema::dropIfExists('driver_profiles');
        Schema::dropIfExists('provider_profiles');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('admin_profiles');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};