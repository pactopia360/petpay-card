<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->create('commerce_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();

            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->text('business_address')->nullable();
            $table->decimal('business_latitude', 11, 8)->nullable();
            $table->decimal('business_longitude', 11, 8)->nullable();

            $table->boolean('sells_products')->default(true);
            $table->boolean('offers_services')->default(false);
            $table->boolean('has_own_delivery')->default(false);
            $table->boolean('uses_petpay_delivery')->default(true);

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending');

            $table->enum('status', [
                'active',
                'pending',
                'blocked',
                'suspended',
                'rejected',
            ])->default('pending');

            $table->boolean('is_open')->default(false);
            $table->decimal('commission_percent', 5, 2)->default(0);

            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('approval_status');
            $table->index('status');
            $table->index('business_name');
        });

        Schema::connection($this->connection)->create('commerce_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection($this->connection)->create('commerce_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('commerce_sessions');
        Schema::connection($this->connection)->dropIfExists('commerce_password_reset_tokens');
        Schema::connection($this->connection)->dropIfExists('commerce_users');
    }
};