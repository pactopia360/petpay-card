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
            ->create('driver_vehicle_3d_jobs', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('driver_vehicle_id')->index();
                $table->unsignedBigInteger('driver_user_id')->index();

                $table->enum('source_type', [
                    'photos',
                    'video',
                ])->default('photos');

                $table->enum('status', [
                    'awaiting_capture',
                    'capture_ready',
                    'queued',
                    'processing',
                    'optimizing',
                    'ready',
                    'requires_recapture',
                    'failed',
                    'rejected',
                ])->default('awaiting_capture')->index();

                $table->string('engine', 50)
                    ->default('pending')
                    ->index();

                $table->unsignedTinyInteger('progress')
                    ->default(0);

                $table->unsignedSmallInteger('required_frames')
                    ->default(30);

                $table->unsignedSmallInteger('captured_frames')
                    ->default(0);

                $table->decimal('quality_score', 5, 2)
                    ->nullable();

                $table->string('model_glb_path')->nullable();
                $table->string('model_glb_sha256', 64)->nullable();
                $table->unsignedBigInteger('model_glb_size')->nullable();

                $table->string('poster_path')->nullable();
                $table->string('map_icon_path')->nullable();

                $table->text('error_message')->nullable();
                $table->json('quality_report')->nullable();
                $table->json('metadata')->nullable();

                $table->timestamp('capture_completed_at')->nullable();
                $table->timestamp('processing_started_at')->nullable();
                $table->timestamp('processing_completed_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('driver_vehicle_id')
                    ->references('id')
                    ->on('driver_vehicles')
                    ->cascadeOnDelete();

                $table->index(
                    ['driver_vehicle_id', 'status'],
                    'driver_vehicle_3d_vehicle_status'
                );
            });

        Schema::connection($this->connection)
            ->create('driver_vehicle_3d_frames', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('driver_vehicle_3d_job_id')->index();
                $table->unsignedBigInteger('driver_vehicle_id')->index();
                $table->unsignedBigInteger('driver_user_id')->index();

                $table->unsignedSmallInteger('sequence');
                $table->decimal('angle_degrees', 6, 2)->nullable();
                $table->enum('elevation', [
                    'low',
                    'middle',
                    'high',
                ])->default('middle');

                $table->string('path');
                $table->string('thumbnail_path')->nullable();
                $table->string('original_name');
                $table->string('mime_type', 120)
                    ->default('image/png');

                $table->string('sha256', 64)->index();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();

                $table->decimal('blur_score', 8, 3)->nullable();
                $table->decimal('brightness_score', 8, 3)->nullable();
                $table->decimal('overlap_score', 8, 3)->nullable();
                $table->boolean('accepted')->default(true)->index();
                $table->string('rejection_reason')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    ['driver_vehicle_3d_job_id', 'sequence'],
                    'driver_vehicle_3d_job_sequence_unique'
                );

                $table->foreign('driver_vehicle_3d_job_id')
                    ->references('id')
                    ->on('driver_vehicle_3d_jobs')
                    ->cascadeOnDelete();

                $table->foreign('driver_vehicle_id')
                    ->references('id')
                    ->on('driver_vehicles')
                    ->cascadeOnDelete();
            });
    }

    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('driver_vehicle_3d_frames');

        Schema::connection($this->connection)
            ->dropIfExists('driver_vehicle_3d_jobs');
    }
};
