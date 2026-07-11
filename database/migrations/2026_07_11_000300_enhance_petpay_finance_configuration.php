<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_payments';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasColumn('commerce_tax_profiles', 'person_type')) {
            $schema->table('commerce_tax_profiles', function (Blueprint $table): void {
                $table->string('person_type', 20)->nullable()->after('commerce_user_id');
                $table->string('fiscal_street', 180)->nullable()->after('tax_email');
                $table->string('fiscal_number', 30)->nullable()->after('fiscal_street');
                $table->string('fiscal_colony', 120)->nullable()->after('fiscal_number');
                $table->string('fiscal_city', 120)->nullable()->after('fiscal_colony');
                $table->string('fiscal_state', 120)->nullable()->after('fiscal_city');
                $table->string('environment', 20)->default('sandbox')->after('fiscal_state');
                $table->string('compliance_opinion_path')->nullable()->after('csf_path');
                $table->string('efirma_cer_path')->nullable()->after('compliance_opinion_path');
                $table->string('efirma_key_path')->nullable()->after('efirma_cer_path');
                $table->text('efirma_password_encrypted')->nullable()->after('efirma_key_path');
                $table->date('efirma_valid_from')->nullable()->after('efirma_password_encrypted');
                $table->date('efirma_valid_to')->nullable()->after('efirma_valid_from');
                $table->string('csd_cer_path')->nullable()->after('efirma_valid_to');
                $table->string('csd_key_path')->nullable()->after('csd_cer_path');
                $table->text('csd_password_encrypted')->nullable()->after('csd_key_path');
                $table->string('csd_certificate_number', 50)->nullable()->after('csd_password_encrypted');
                $table->date('csd_valid_from')->nullable()->after('csd_certificate_number');
                $table->date('csd_valid_to')->nullable()->after('csd_valid_from');
            });
        }

        if (! $schema->hasColumn('commerce_bank_accounts', 'bank_code')) {
            $schema->table('commerce_bank_accounts', function (Blueprint $table): void {
                $table->string('bank_code', 10)->nullable()->after('commerce_user_id');
                $table->string('holder_rfc', 13)->nullable()->after('account_holder');
                $table->text('account_number_encrypted')->nullable()->after('clabe_encrypted');
                $table->string('account_number_last4', 4)->nullable()->after('account_last4');
                $table->string('card_last4', 4)->nullable()->after('account_number_last4');
                $table->string('bank_branch', 120)->nullable()->after('card_last4');
                $table->string('agreement_reference', 120)->nullable()->after('bank_branch');
                $table->string('statement_path')->nullable()->after('proof_path');
                $table->boolean('is_active')->default(true)->after('is_primary')->index();
                $table->text('rejection_reason')->nullable()->after('verified_at');
            });
        }

        if (! $schema->hasColumn('finance_disputes', 'priority')) {
            $schema->table('finance_disputes', function (Blueprint $table): void {
                $table->string('priority', 20)->default('normal')->after('type')->index();
                $table->unsignedBigInteger('payment_transaction_id')->nullable()->after('order_id')->index();
            });
        }

        if (! $schema->hasTable('commerce_invoice_series')) {
            $schema->create('commerce_invoice_series', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('commerce_user_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('series', 20);
                $table->string('cfdi_type', 3)->index();
                $table->unsignedBigInteger('initial_folio')->default(1);
                $table->unsignedBigInteger('current_folio')->default(1);
                $table->string('environment', 20)->default('sandbox')->index();
                $table->boolean('is_default')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->unique(
                    ['commerce_user_id', 'branch_id', 'series', 'cfdi_type', 'environment'],
                    'commerce_invoice_series_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('commerce_invoice_series');
    }
};
