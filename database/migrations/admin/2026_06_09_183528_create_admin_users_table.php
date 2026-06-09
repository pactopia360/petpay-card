<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_admin';

    public function up(): void
    {
        Schema::connection('mysql_admin')->create('admin_users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->index();

            $table->string('position', 120)->nullable();
            $table->string('department', 120)->nullable();
            $table->boolean('can_manage_system')->default(false);

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

        Schema::connection('mysql_admin')->create('admin_password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('mysql_admin')->create('admin_sessions', function (Blueprint $table) {
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
        Schema::connection('mysql_admin')->dropIfExists('admin_sessions');
        Schema::connection('mysql_admin')->dropIfExists('admin_password_reset_tokens');
        Schema::connection('mysql_admin')->dropIfExists('admin_users');
    }
};