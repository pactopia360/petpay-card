<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_comercio')->create('commerce_brandings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commerce_user_id')->unique();

            $table->string('store_name', 160)->nullable();
            $table->string('slogan', 220)->nullable();
            $table->text('description')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();

            $table->string('primary_color', 7)->default('#F97316');
            $table->string('secondary_color', 7)->default('#111827');
            $table->string('accent_color', 7)->default('#F4EFE3');
            $table->string('background_color', 7)->default('#FFFDF8');
            $table->string('button_text_color', 7)->default('#FFFFFF');

            $table->boolean('show_logo')->default(true);
            $table->boolean('show_banner')->default(true);

            $table->timestamps();

            $table->index('commerce_user_id', 'commerce_brandings_user_index');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_comercio')->dropIfExists('commerce_brandings');
    }
};
