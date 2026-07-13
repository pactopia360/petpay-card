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
        $this->addEmergencyContactColumns();
        $this->addPersonalReferenceColumns();

        $this->ensureIndexes();
    }

    public function down(): void
    {
        $this->dropIndexes();
        $this->dropPersonalReferenceColumns();
        $this->dropEmergencyContactColumns();
    }

    private function addEmergencyContactColumns(): void
    {
        $schema = Schema::connection($this->connection);
        $tableName = 'driver_emergency_contacts';

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $this->addColumnIfMissing(
            $tableName,
            'relationship_code',
            static function (Blueprint $table): void {
                $table->string('relationship_code', 50)
                    ->nullable()
                    ->after('relationship');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'phone_normalized',
            static function (Blueprint $table): void {
                $table->string('phone_normalized', 20)
                    ->nullable()
                    ->after('phone');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'phone_hash',
            static function (Blueprint $table): void {
                $table->string('phone_hash', 64)
                    ->nullable()
                    ->after('phone_normalized');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'alternate_phone_normalized',
            static function (Blueprint $table): void {
                $table->string('alternate_phone_normalized', 20)
                    ->nullable()
                    ->after('alternate_phone');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'contact_consent',
            static function (Blueprint $table): void {
                $table->boolean('contact_consent')
                    ->default(false)
                    ->after('email');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'preferred_contact_time',
            static function (Blueprint $table): void {
                $table->string('preferred_contact_time', 40)
                    ->nullable()
                    ->after('contact_consent');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'verification_status',
            static function (Blueprint $table): void {
                $table->enum('verification_status', [
                    'pending',
                    'sent',
                    'verified',
                    'failed',
                    'declined',
                    'blocked',
                    'manual_review',
                ])
                    ->default('pending')
                    ->after('is_verified');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'verification_attempts',
            static function (Blueprint $table): void {
                $table->unsignedTinyInteger('verification_attempts')
                    ->default(0)
                    ->after('verification_status');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'last_verification_at',
            static function (Blueprint $table): void {
                $table->timestamp('last_verification_at')
                    ->nullable()
                    ->after('verified_at');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'risk_status',
            static function (Blueprint $table): void {
                $table->enum('risk_status', [
                    'normal',
                    'review',
                    'blocked',
                ])
                    ->default('normal')
                    ->after('last_verification_at');
            }
        );
    }

    private function addPersonalReferenceColumns(): void
    {
        $schema = Schema::connection($this->connection);
        $tableName = 'driver_personal_references';

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $this->addColumnIfMissing(
            $tableName,
            'reference_type',
            static function (Blueprint $table): void {
                $table->enum('reference_type', [
                    'family',
                    'non_family',
                ])
                    ->nullable()
                    ->after('position');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'relationship_code',
            static function (Blueprint $table): void {
                $table->string('relationship_code', 50)
                    ->nullable()
                    ->after('relationship');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'years_known',
            static function (Blueprint $table): void {
                $table->unsignedTinyInteger('years_known')
                    ->nullable()
                    ->after('relationship_code');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'lives_same_address',
            static function (Blueprint $table): void {
                $table->boolean('lives_same_address')
                    ->default(false)
                    ->after('years_known');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'contact_consent',
            static function (Blueprint $table): void {
                $table->boolean('contact_consent')
                    ->default(false)
                    ->after('lives_same_address');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'preferred_contact_time',
            static function (Blueprint $table): void {
                $table->string('preferred_contact_time', 40)
                    ->nullable()
                    ->after('contact_consent');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'phone_normalized',
            static function (Blueprint $table): void {
                $table->string('phone_normalized', 20)
                    ->nullable()
                    ->after('phone');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'phone_hash',
            static function (Blueprint $table): void {
                $table->string('phone_hash', 64)
                    ->nullable()
                    ->after('phone_normalized');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'alternate_phone_normalized',
            static function (Blueprint $table): void {
                $table->string('alternate_phone_normalized', 20)
                    ->nullable()
                    ->after('alternate_phone');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'verification_status',
            static function (Blueprint $table): void {
                $table->enum('verification_status', [
                    'pending',
                    'sent',
                    'verified',
                    'failed',
                    'declined',
                    'blocked',
                    'manual_review',
                ])
                    ->default('pending')
                    ->after('is_verified');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'verification_attempts',
            static function (Blueprint $table): void {
                $table->unsignedTinyInteger('verification_attempts')
                    ->default(0)
                    ->after('verification_status');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'last_verification_at',
            static function (Blueprint $table): void {
                $table->timestamp('last_verification_at')
                    ->nullable()
                    ->after('verified_at');
            }
        );

        $this->addColumnIfMissing(
            $tableName,
            'risk_status',
            static function (Blueprint $table): void {
                $table->enum('risk_status', [
                    'normal',
                    'review',
                    'blocked',
                ])
                    ->default('normal')
                    ->after('last_verification_at');
            }
        );
    }

    private function ensureIndexes(): void
    {
        $this->addIndexIfMissing(
            'driver_emergency_contacts',
            ['phone_normalized'],
            'driver_emergency_contacts_phone_normalized_index'
        );

        $this->addIndexIfMissing(
            'driver_emergency_contacts',
            ['phone_hash'],
            'driver_emergency_contacts_phone_hash_index'
        );

        $this->addIndexIfMissing(
            'driver_emergency_contacts',
            ['verification_status'],
            'driver_emergency_contacts_verification_status_index'
        );

        $this->addIndexIfMissing(
            'driver_emergency_contacts',
            ['risk_status'],
            'driver_emergency_contacts_risk_status_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['reference_type'],
            'driver_personal_references_reference_type_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['phone_normalized'],
            'driver_personal_references_phone_normalized_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['phone_hash'],
            'driver_personal_references_phone_hash_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['verification_status'],
            'driver_personal_references_verification_status_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['risk_status'],
            'driver_personal_references_risk_status_index'
        );

        $this->addIndexIfMissing(
            'driver_personal_references',
            ['driver_user_id', 'phone_hash'],
            'driver_reference_phone_lookup'
        );
    }

    private function dropIndexes(): void
    {
        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_reference_phone_lookup'
        );

        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_personal_references_risk_status_index'
        );

        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_personal_references_verification_status_index'
        );

        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_personal_references_phone_hash_index'
        );

        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_personal_references_phone_normalized_index'
        );

        $this->dropIndexIfExists(
            'driver_personal_references',
            'driver_personal_references_reference_type_index'
        );

        $this->dropIndexIfExists(
            'driver_emergency_contacts',
            'driver_emergency_contacts_risk_status_index'
        );

        $this->dropIndexIfExists(
            'driver_emergency_contacts',
            'driver_emergency_contacts_verification_status_index'
        );

        $this->dropIndexIfExists(
            'driver_emergency_contacts',
            'driver_emergency_contacts_phone_hash_index'
        );

        $this->dropIndexIfExists(
            'driver_emergency_contacts',
            'driver_emergency_contacts_phone_normalized_index'
        );
    }

    private function dropPersonalReferenceColumns(): void
    {
        $this->dropColumnsIfPresent(
            'driver_personal_references',
            [
                'reference_type',
                'relationship_code',
                'years_known',
                'lives_same_address',
                'contact_consent',
                'preferred_contact_time',
                'phone_normalized',
                'phone_hash',
                'alternate_phone_normalized',
                'verification_status',
                'verification_attempts',
                'last_verification_at',
                'risk_status',
            ]
        );
    }

    private function dropEmergencyContactColumns(): void
    {
        $this->dropColumnsIfPresent(
            'driver_emergency_contacts',
            [
                'relationship_code',
                'phone_normalized',
                'phone_hash',
                'alternate_phone_normalized',
                'contact_consent',
                'preferred_contact_time',
                'verification_status',
                'verification_attempts',
                'last_verification_at',
                'risk_status',
            ]
        );
    }

    private function addColumnIfMissing(
        string $tableName,
        string $columnName,
        callable $definition
    ): void {
        $schema = Schema::connection($this->connection);

        if (
            ! $schema->hasTable($tableName)
            || $schema->hasColumn($tableName, $columnName)
        ) {
            return;
        }

        $schema->table(
            $tableName,
            function (Blueprint $table) use ($definition): void {
                $definition($table);
            }
        );
    }

    private function addIndexIfMissing(
        string $tableName,
        array $columns,
        string $indexName
    ): void {
        $schema = Schema::connection($this->connection);

        if (
            ! $schema->hasTable($tableName)
            || $this->indexExists($tableName, $indexName)
        ) {
            return;
        }

        foreach ($columns as $column) {
            if (! $schema->hasColumn($tableName, $column)) {
                return;
            }
        }

        $schema->table(
            $tableName,
            static function (Blueprint $table) use (
                $columns,
                $indexName
            ): void {
                $table->index($columns, $indexName);
            }
        );
    }

    private function dropIndexIfExists(
        string $tableName,
        string $indexName
    ): void {
        $schema = Schema::connection($this->connection);

        if (
            ! $schema->hasTable($tableName)
            || ! $this->indexExists($tableName, $indexName)
        ) {
            return;
        }

        $schema->table(
            $tableName,
            static function (Blueprint $table) use (
                $indexName
            ): void {
                $table->dropIndex($indexName);
            }
        );
    }

    private function dropColumnsIfPresent(
        string $tableName,
        array $columns
    ): void {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(
            array_filter(
                $columns,
                static fn (string $column): bool =>
                    $schema->hasColumn($tableName, $column)
            )
        );

        if ($existingColumns === []) {
            return;
        }

        $schema->table(
            $tableName,
            static function (Blueprint $table) use (
                $existingColumns
            ): void {
                $table->dropColumn($existingColumns);
            }
        );
    }

    private function indexExists(
        string $tableName,
        string $indexName
    ): bool {
        $result = DB::connection($this->connection)
            ->selectOne(
                <<<'SQL'
                    SELECT COUNT(*) AS aggregate
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                      AND table_name = ?
                      AND index_name = ?
                SQL,
                [
                    $tableName,
                    $indexName,
                ]
            );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};