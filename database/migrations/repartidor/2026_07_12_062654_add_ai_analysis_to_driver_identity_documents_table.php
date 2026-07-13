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
            ->table('driver_identity_documents', function (Blueprint $table): void {
                $table->string('analysis_status', 30)
                    ->default('pending')
                    ->after('status');

                $table->string('analysis_provider', 50)
                    ->nullable()
                    ->after('analysis_status');

                $table->string('analysis_model', 100)
                    ->nullable()
                    ->after('analysis_provider');

                $table->string('ai_response_id', 190)
                    ->nullable()
                    ->after('analysis_model');

                $table->string('detected_document_type', 100)
                    ->nullable()
                    ->after('ai_response_id');

                $table->decimal('analysis_confidence', 5, 2)
                    ->nullable()
                    ->after('detected_document_type');

                $table->decimal('quality_score', 5, 2)
                    ->nullable()
                    ->after('analysis_confidence');

                $table->unsignedInteger('image_width')
                    ->nullable()
                    ->after('quality_score');

                $table->unsignedInteger('image_height')
                    ->nullable()
                    ->after('image_width');

                $table->unsignedSmallInteger('face_count')
                    ->nullable()
                    ->after('image_height');

                $table->boolean('face_detected')
                    ->nullable()
                    ->after('face_count');

                $table->json('extracted_data')
                    ->nullable()
                    ->after('face_detected');

                $table->json('validation_results')
                    ->nullable()
                    ->after('extracted_data');

                $table->json('analysis_warnings')
                    ->nullable()
                    ->after('validation_results');

                $table->text('analysis_error')
                    ->nullable()
                    ->after('analysis_warnings');

                $table->timestamp('analyzed_at')
                    ->nullable()
                    ->after('analysis_error');

                $table->index(
                    ['driver_user_id', 'analysis_status'],
                    'driver_documents_analysis_status_idx'
                );
            });
    }

    public function down(): void
    {
        Schema::connection($this->connection)
            ->table('driver_identity_documents', function (Blueprint $table): void {
                $table->dropIndex('driver_documents_analysis_status_idx');

                $table->dropColumn([
                    'analysis_status',
                    'analysis_provider',
                    'analysis_model',
                    'ai_response_id',
                    'detected_document_type',
                    'analysis_confidence',
                    'quality_score',
                    'image_width',
                    'image_height',
                    'face_count',
                    'face_detected',
                    'extracted_data',
                    'validation_results',
                    'analysis_warnings',
                    'analysis_error',
                    'analyzed_at',
                ]);
            });
    }
};