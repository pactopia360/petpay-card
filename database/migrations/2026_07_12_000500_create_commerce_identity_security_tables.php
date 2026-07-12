<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->create('commerce_identity_profiles', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('commerce_user_id')->unique()->index();
            $table->enum('person_type', ['individual', 'company'])->default('individual');
            $table->string('business_rfc', 20)->nullable()->index();
            $table->string('business_legal_name', 190)->nullable();
            $table->string('representative_name', 190)->nullable();
            $table->string('representative_rfc', 20)->nullable()->index();
            $table->string('representative_curp', 25)->nullable();
            $table->string('representative_email', 190)->nullable();
            $table->string('representative_phone', 30)->nullable();
            $table->string('representative_position', 120)->nullable();
            $table->string('address_line', 500)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('municipality', 120)->nullable();
            $table->string('notarial_deed_number', 120)->nullable();
            $table->string('notary_name', 190)->nullable();
            $table->string('notary_number', 50)->nullable();
            $table->string('legal_powers_scope', 500)->nullable();
            $table->boolean('powers_declared_current')->default(false);
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

            $table->index(['status', 'submitted_at']);
        });

        Schema::connection($this->connection)->create('commerce_identity_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('identity_profile_id')->index();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->string('document_type', 100)->index();
            $table->string('original_name', 255);
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64);
            $table->boolean('is_required')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected', 'replaced'])->default('pending')->index();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->index(['identity_profile_id', 'document_type', 'status'], 'identity_document_lookup');
        });

        Schema::connection($this->connection)->create('commerce_identity_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('identity_profile_id')->index();
            $table->unsignedBigInteger('commerce_user_id')->index();
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
        Schema::connection($this->connection)->dropIfExists('commerce_identity_events');
        Schema::connection($this->connection)->dropIfExists('commerce_identity_documents');
        Schema::connection($this->connection)->dropIfExists('commerce_identity_profiles');
    }
};
