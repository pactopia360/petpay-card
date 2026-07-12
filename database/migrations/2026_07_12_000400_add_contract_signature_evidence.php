<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->table('commerce_contracts', function (Blueprint $table): void {
            $columns = [
                'signature_image_path' => fn () => $table->string('signature_image_path')->nullable()->after('signature_method'),
                'camera_evidence_path' => fn () => $table->string('camera_evidence_path')->nullable()->after('signature_image_path'),
                'certificate_rfc' => fn () => $table->string('certificate_rfc', 20)->nullable()->after('camera_evidence_path'),
                'certificate_serial' => fn () => $table->string('certificate_serial', 160)->nullable()->after('certificate_rfc'),
                'certificate_subject' => fn () => $table->text('certificate_subject')->nullable()->after('certificate_serial'),
                'certificate_valid_from' => fn () => $table->timestamp('certificate_valid_from')->nullable()->after('certificate_subject'),
                'certificate_valid_to' => fn () => $table->timestamp('certificate_valid_to')->nullable()->after('certificate_valid_from'),
                'cryptographic_signature' => fn () => $table->longText('cryptographic_signature')->nullable()->after('certificate_valid_to'),
                'signature_metadata' => fn () => $table->json('signature_metadata')->nullable()->after('cryptographic_signature'),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', $name)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('commerce_contracts', function (Blueprint $table): void {
            foreach ([
                'signature_image_path',
                'camera_evidence_path',
                'certificate_rfc',
                'certificate_serial',
                'certificate_subject',
                'certificate_valid_from',
                'certificate_valid_to',
                'cryptographic_signature',
                'signature_metadata',
            ] as $column) {
                if (Schema::connection($this->connection)->hasColumn('commerce_contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
