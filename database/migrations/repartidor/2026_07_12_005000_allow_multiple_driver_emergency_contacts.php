<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_repartidor';

    public function up(): void
    {
        $connection = DB::connection($this->connection);
        $database = $connection->getDatabaseName();

        $uniqueIndex = $connection->selectOne(
            <<<'SQL'
                SELECT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'driver_emergency_contacts'
                  AND COLUMN_NAME = 'driver_user_id'
                  AND NON_UNIQUE = 0
                  AND INDEX_NAME <> 'PRIMARY'
                LIMIT 1
            SQL,
            [$database]
        );

        if ($uniqueIndex !== null) {
            $indexName = str_replace('`', '', (string) $uniqueIndex->INDEX_NAME);

            $connection->statement(
                "ALTER TABLE driver_emergency_contacts DROP INDEX `{$indexName}`"
            );
        }

        Schema::connection($this->connection)
            ->table('driver_emergency_contacts', function (Blueprint $table): void {
                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'position'
                )) {
                    $table->unsignedTinyInteger('position')
                        ->default(1)
                        ->after('driver_user_id');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'relationship_code'
                )) {
                    $table->string('relationship_code', 50)
                        ->nullable()
                        ->after('relationship');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'lives_same_address'
                )) {
                    $table->boolean('lives_same_address')
                        ->default(false)
                        ->after('relationship_code');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'phone_normalized'
                )) {
                    $table->string('phone_normalized', 20)
                        ->nullable()
                        ->after('phone');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'phone_hash'
                )) {
                    $table->string('phone_hash', 64)
                        ->nullable()
                        ->after('phone_normalized');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'alternate_phone_normalized'
                )) {
                    $table->string('alternate_phone_normalized', 20)
                        ->nullable()
                        ->after('alternate_phone');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'contact_consent'
                )) {
                    $table->boolean('contact_consent')
                        ->default(false)
                        ->after('email');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'preferred_contact_time'
                )) {
                    $table->string('preferred_contact_time', 40)
                        ->nullable()
                        ->after('contact_consent');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'verification_status'
                )) {
                    $table->string('verification_status', 40)
                        ->default('pending')
                        ->after('is_verified');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'verification_attempts'
                )) {
                    $table->unsignedTinyInteger('verification_attempts')
                        ->default(0)
                        ->after('verification_status');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'last_verification_at'
                )) {
                    $table->timestamp('last_verification_at')
                        ->nullable()
                        ->after('verified_at');
                }

                if (! Schema::connection('mysql_repartidor')->hasColumn(
                    'driver_emergency_contacts',
                    'risk_status'
                )) {
                    $table->string('risk_status', 30)
                        ->default('normal')
                        ->after('last_verification_at');
                }
            });

        $connection
            ->table('driver_emergency_contacts')
            ->whereNull('position')
            ->update(['position' => 1]);

        $compoundIndex = $connection->selectOne(
            <<<'SQL'
                SELECT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'driver_emergency_contacts'
                  AND INDEX_NAME = 'driver_emergency_position_unique'
                LIMIT 1
            SQL,
            [$database]
        );

        if ($compoundIndex === null) {
            $connection->statement(
                'ALTER TABLE driver_emergency_contacts
                 ADD UNIQUE INDEX driver_emergency_position_unique
                 (driver_user_id, position)'
            );
        }

        if (! Schema::connection($this->connection)->hasColumn(
            'driver_personal_references',
            'relationship_code'
        )) {
            Schema::connection($this->connection)
                ->table('driver_personal_references', function (Blueprint $table): void {
                    $table->string('relationship_code', 50)
                        ->nullable()
                        ->after('relationship');
                });
        }

        if (! Schema::connection($this->connection)->hasColumn(
            'driver_personal_references',
            'phone_normalized'
        )) {
            Schema::connection($this->connection)
                ->table('driver_personal_references', function (Blueprint $table): void {
                    $table->string('phone_normalized', 20)
                        ->nullable()
                        ->after('phone');
                });
        }
    }

    public function down(): void
    {
        $connection = DB::connection($this->connection);

        $connection->statement(
            'ALTER TABLE driver_emergency_contacts
             DROP INDEX driver_emergency_position_unique'
        );

        Schema::connection($this->connection)
            ->table('driver_emergency_contacts', function (Blueprint $table): void {
                $table->dropColumn([
                    'position',
                    'relationship_code',
                    'lives_same_address',
                    'phone_normalized',
                    'phone_hash',
                    'alternate_phone_normalized',
                    'contact_consent',
                    'preferred_contact_time',
                    'verification_status',
                    'verification_attempts',
                    'last_verification_at',
                    'risk_status',
                ]);
            });
    }
};
