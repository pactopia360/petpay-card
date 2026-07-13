<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        Schema::connection($this->connection)
            ->create('vehicle_catalog_makes', function (Blueprint $table): void {
                $table->id();
                $table->string('vehicle_type', 50)->index();
                $table->string('name', 120);
                $table->string('slug', 140);
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(
                    ['vehicle_type', 'slug'],
                    'vehicle_make_type_slug_unique'
                );
            });

        Schema::connection($this->connection)
            ->create('vehicle_catalog_models', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_catalog_make_id')->index();
                $table->string('name', 120);
                $table->string('slug', 140);
                $table->unsignedSmallInteger('year_from')->nullable();
                $table->unsignedSmallInteger('year_to')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(
                    ['vehicle_catalog_make_id', 'slug'],
                    'vehicle_model_make_slug_unique'
                );

                $table->foreign('vehicle_catalog_make_id')
                    ->references('id')
                    ->on('vehicle_catalog_makes')
                    ->cascadeOnDelete();
            });

        Schema::connection($this->connection)
            ->create('driver_vehicle_photos', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('driver_vehicle_id')->index();
                $table->unsignedBigInteger('driver_user_id')->index();

                $table->enum('position', [
                    'front',
                    'front_left',
                    'left',
                    'rear',
                    'right',
                    'front_right',
                    'plate',
                    'dashboard',
                ])->index();

                $table->string('path');
                $table->string('thumbnail_path')->nullable();
                $table->string('original_name');
                $table->string('original_mime_type', 120)->nullable();
                $table->string('mime_type', 120)->default('image/png');
                $table->string('sha256', 64)->index();

                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();

                $table->unsignedSmallInteger('sequence')->default(0);
                $table->boolean('is_plate_visible')->default(false);
                $table->boolean('is_primary')->default(false)->index();

                $table->enum('status', [
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                ])->default('pending')->index();

                $table->text('review_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    ['driver_vehicle_id', 'position'],
                    'driver_vehicle_photo_position_unique'
                );

                $table->index(
                    ['driver_vehicle_id', 'sequence'],
                    'driver_vehicle_photo_sequence'
                );

                $table->foreign('driver_vehicle_id')
                    ->references('id')
                    ->on('driver_vehicles')
                    ->cascadeOnDelete();
            });
    }

    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('driver_vehicle_photos');

        Schema::connection($this->connection)
            ->dropIfExists('vehicle_catalog_models');

        Schema::connection($this->connection)
            ->dropIfExists('vehicle_catalog_makes');
    }
};
