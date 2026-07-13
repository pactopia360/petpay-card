<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        /*
        |--------------------------------------------------------------------------
        | Protección para instalaciones existentes
        |--------------------------------------------------------------------------
        |
        | La tabla ya fue creada anteriormente, pero esta migración no quedó
        | registrada en la tabla migrations. No debemos eliminarla ni volverla
        | a crear porque contiene información de verificaciones telefónicas.
        |
        */

        if ($schema->hasTable('driver_phone_verifications')) {
            return;
        }

        $schema->create(
            'driver_phone_verifications',
            function (Blueprint $table): void {
                $table->id();

                $table->unsignedBigInteger('driver_user_id')
                    ->index();

                $table->unsignedBigInteger('identity_profile_id')
                    ->nullable()
                    ->index();

                $table->string('target_type', 50)
                    ->default('driver')
                    ->index();

                $table->unsignedBigInteger('target_id')
                    ->nullable()
                    ->index();

                $table->string('phone', 30);
                $table->string('phone_masked', 30);

                $table->string('phone_hash', 64)
                    ->index();

                $table->string('channel', 30)
                    ->default('voice');

                $table->string('provider', 50)
                    ->default('fake');

                $table->string('provider_reference', 190)
                    ->nullable();

                $table->string('code_hash');

                $table->enum('status', [
                    'sent',
                    'verified',
                    'failed',
                    'expired',
                    'blocked',
                    'cancelled',
                ])
                    ->default('sent')
                    ->index();

                $table->unsignedTinyInteger('verification_attempts')
                    ->default(0);

                $table->timestamp('sent_at')
                    ->nullable();

                $table->timestamp('expires_at')
                    ->nullable()
                    ->index();

                $table->timestamp('verified_at')
                    ->nullable();

                $table->timestamp('locked_until')
                    ->nullable();

                $table->string('ip_address', 64)
                    ->nullable();

                $table->text('user_agent')
                    ->nullable();

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'driver_user_id',
                        'target_type',
                        'status',
                    ],
                    'driver_phone_verification_status'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('driver_phone_verifications');
    }
};