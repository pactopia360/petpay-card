<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection('mysql_comercio')->create('commerce_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commerce_user_id')
                ->constrained('commerce_users')
                ->cascadeOnDelete();

            $table->string('chain_name');
            $table->string('branch_name');
            $table->string('branch_code', 50)->nullable();

            $table->string('google_coordinates')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->string('street');
            $table->string('neighborhood')->nullable();
            $table->string('postal_code', 12)->nullable();
            $table->string('state')->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp_phone', 20)->nullable();

            $table->json('service_days')->nullable();
            $table->time('service_open_time')->nullable();
            $table->time('service_close_time')->nullable();

            $table->boolean('phone_verified')->default(false);
            $table->boolean('email_verified')->default(false);

            $table->boolean('is_open')->default(true);
            $table->json('missing_fields')->nullable();
            $table->string('status_flag')->default('incomplete');

            $table->timestamps();

            $table->index(['commerce_user_id', 'status_flag']);
            $table->index(['commerce_user_id', 'is_open']);
            $table->index(['commerce_user_id', 'branch_code']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->dropIfExists('commerce_branches');
    }
};