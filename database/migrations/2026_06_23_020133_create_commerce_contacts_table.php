<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection('mysql_comercio')->create('commerce_contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commerce_user_id')
                ->constrained('commerce_users')
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name_paternal')->nullable();
            $table->string('last_name_maternal')->nullable();

            $table->string('street')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('postal_code', 12)->nullable();
            $table->string('state')->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['commerce_user_id', 'is_primary']);
            $table->index(['commerce_user_id', 'email']);
            $table->index(['commerce_user_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->dropIfExists('commerce_contacts');
    }
};