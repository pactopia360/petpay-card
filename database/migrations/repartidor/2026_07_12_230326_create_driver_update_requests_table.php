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
            ->create('driver_update_requests', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->unsignedBigInteger('driver_user_id')->index();
                $table->unsignedBigInteger('identity_profile_id')
                    ->nullable()
                    ->index();

                $table->string('field_name', 100)->index();
                $table->text('current_value')->nullable();
                $table->text('requested_value');
                $table->text('reason');

                $table->string('evidence_path')->nullable();
                $table->string('evidence_original_name')->nullable();
                $table->string('evidence_mime_type', 120)->nullable();
                $table->string('evidence_sha256', 64)->nullable();

                $table->enum('status', [
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                    'cancelled',
                ])->default('pending')->index();

                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('applied_at')->nullable();

                $table->timestamps();

                $table->index(
                    ['driver_user_id', 'field_name', 'status'],
                    'driver_update_request_lookup'
                );
            });
    }

    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('driver_update_requests');
    }
};
