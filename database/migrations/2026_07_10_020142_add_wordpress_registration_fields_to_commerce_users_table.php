<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_comercio')
            ->table('commerce_users', function (Blueprint $table) {
                $table->string('floor_office', 120)
                    ->nullable()
                    ->after('business_address');

                $table->string('brand_name', 180)
                    ->nullable()
                    ->after('business_name');

                $table->string('website_url', 500)
                    ->nullable()
                    ->after('business_email');

                $table->boolean('whatsapp_enabled')
                    ->default(false)
                    ->after('website_url');

                $table->timestamp('terms_accepted_at')
                    ->nullable()
                    ->after('whatsapp_enabled');
            });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')
            ->table('commerce_users', function (Blueprint $table) {
                $table->dropColumn([
                    'floor_office',
                    'brand_name',
                    'website_url',
                    'whatsapp_enabled',
                    'terms_accepted_at',
                ]);
            });
    }
};