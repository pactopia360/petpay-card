<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        Schema::connection($this->connection)->create('driver_identity_profiles', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('driver_user_id')->unique()->index();

            $table->string('paternal_last_name', 120)->nullable();
            $table->string('maternal_last_name', 120)->nullable();
            $table->string('curp', 25)->nullable()->index();

            $table->string('home_phone', 30)->nullable();
            $table->string('mobile_phone', 30)->nullable();
            $table->string('contact_email', 190)->nullable();

            $table->boolean('phone_verified')->default(false);
            $table->timestamp('phone_verified_at')->nullable();

            $table->boolean('email_verified')->default(false);
            $table->timestamp('contact_email_verified_at')->nullable();

            $table->boolean('data_processing_consent')->default(false);
            $table->boolean('truth_declaration')->default(false);

            $table->string('liveness_challenge', 190)->nullable();
            $table->string('liveness_evidence_path')->nullable();
            $table->string('liveness_sha256', 64)->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'corrections_required',
                'approved',
                'rejected',
            ])->default('draft')->index();

            $table->text('review_notes')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('review_started_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();

            $table->timestamp('identity_locked_at')->nullable();
            $table->string('identity_hash', 64)->nullable();

            $table->timestamps();

            $table->index(['status', 'submitted_at'], 'driver_identity_status_submitted');
        });

        Schema::connection($this->connection)->create('driver_addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('driver_user_id')->index();

            $table->string('country', 100)->default('México');
            $table->string('postal_code', 10)->nullable()->index();
            $table->string('state', 120)->nullable();
            $table->string('municipality', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('neighborhood', 180)->nullable();

            $table->string('street', 190)->nullable();
            $table->string('exterior_number', 40)->nullable();
            $table->string('interior_number', 40)->nullable();
            $table->string('references', 500)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['driver_user_id', 'is_primary', 'is_active'],
                'driver_address_lookup'
            );
        });

        Schema::connection($this->connection)->create('driver_emergency_contacts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('driver_user_id')->unique()->index();

            $table->string('full_name', 190);
            $table->string('relationship', 100);
            $table->string('phone', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email', 190)->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });

        Schema::connection($this->connection)->create('driver_personal_references', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('driver_user_id')->index();

            $table->unsignedTinyInteger('position')->default(1);
            $table->string('full_name', 190);
            $table->string('relationship', 100);
            $table->string('phone', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email', 190)->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['driver_user_id', 'position'],
                'driver_reference_position_unique'
            );
        });

        Schema::connection($this->connection)->create('driver_identity_documents', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('identity_profile_id')->index();
            $table->unsignedBigInteger('driver_user_id')->index();

            $table->string('document_type', 100)->index();
            $table->string('original_name', 255);
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64);

            $table->boolean('is_required')->default(true);

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'replaced',
            ])->default('pending')->index();

            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();

            $table->timestamps();

            $table->index(
                ['identity_profile_id', 'document_type', 'status'],
                'driver_identity_document_lookup'
            );
        });

        Schema::connection($this->connection)->create('driver_identity_events', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('identity_profile_id')->index();
            $table->unsignedBigInteger('driver_user_id')->index();

            $table->string('event_type', 100)->index();
            $table->string('actor_type', 50);
            $table->unsignedBigInteger('actor_id')->nullable();

            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('occurred_at')->useCurrent()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('driver_identity_events');
        Schema::connection($this->connection)->dropIfExists('driver_identity_documents');
        Schema::connection($this->connection)->dropIfExists('driver_personal_references');
        Schema::connection($this->connection)->dropIfExists('driver_emergency_contacts');
        Schema::connection($this->connection)->dropIfExists('driver_addresses');
        Schema::connection($this->connection)->dropIfExists('driver_identity_profiles');
    }
};