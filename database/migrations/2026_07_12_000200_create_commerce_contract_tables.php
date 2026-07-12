<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->create('commerce_contracts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('title', 190);
            $table->string('contract_type', 100)->default('commercial');
            $table->string('version', 30)->default('1.0');
            $table->enum('status', ['draft', 'pending_review', 'pending_signature', 'signed', 'rejected', 'expired', 'cancelled'])->default('draft')->index();
            $table->string('representative_name', 190)->nullable();
            $table->string('representative_email', 190)->nullable();
            $table->string('representative_position', 120)->nullable();
            $table->string('signature_method', 60)->nullable();
            $table->string('original_path')->nullable();
            $table->string('signed_path')->nullable();
            $table->longText('content_html')->nullable();
            $table->string('content_hash', 128)->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_ip', 64)->nullable();
            $table->text('signed_user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commerce_user_id', 'status']);
            $table->index(['effective_from', 'effective_to']);
        });

        Schema::connection($this->connection)->create('commerce_contract_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contract_id')->index();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->string('document_type', 100);
            $table->string('name', 190);
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64)->nullable();
            $table->boolean('is_required')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('commerce_contract_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contract_id')->index();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->string('event_type', 80)->index();
            $table->string('actor_type', 60)->default('commerce');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('commerce_contract_events');
        Schema::connection($this->connection)->dropIfExists('commerce_contract_documents');
        Schema::connection($this->connection)->dropIfExists('commerce_contracts');
    }
};