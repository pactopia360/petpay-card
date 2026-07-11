<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_payments';

    public function up(): void
    {
        Schema::connection($this->connection)->create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('order_folio', 40)->nullable()->index();
            $table->string('provider', 60)->nullable();
            $table->string('provider_reference', 160)->nullable()->index();
            $table->string('payment_method', 50)->nullable()->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 40)->default('pending')->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('finance_movements', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('payment_transaction_id')->nullable()->index();
            $table->unsignedBigInteger('settlement_id')->nullable()->index();
            $table->string('type', 50)->index();
            $table->string('concept', 180);
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->decimal('delivery_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('adjustment_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('pending')->index();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('settlements', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('folio', 50)->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->decimal('withholding_amount', 14, 2)->default(0);
            $table->decimal('adjustment_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('calculating')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('bank_reference', 160)->nullable();
            $table->string('receipt_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('settlement_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('settlement_id')->index();
            $table->unsignedBigInteger('finance_movement_id')->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('finance_refunds', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('payment_transaction_id')->nullable()->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reason', 180)->nullable();
            $table->string('status', 40)->default('requested')->index();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('finance_disputes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('folio', 50)->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('settlement_id')->nullable()->index();
            $table->string('type', 60)->index();
            $table->string('subject', 180);
            $table->text('description');
            $table->decimal('claimed_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('open')->index();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('commerce_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->string('bank_name', 120);
            $table->string('account_holder', 180);
            $table->string('clabe_encrypted', 255);
            $table->string('account_last4', 4)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 40)->default('pending')->index();
            $table->boolean('is_primary')->default(false)->index();
            $table->string('proof_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('commerce_tax_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commerce_user_id')->unique();
            $table->string('rfc', 13)->nullable();
            $table->string('legal_name', 240)->nullable();
            $table->string('tax_regime', 10)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('cfdi_use', 10)->nullable();
            $table->string('tax_email', 180)->nullable();
            $table->string('status', 40)->default('incomplete')->index();
            $table->string('csf_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('commerce_invoices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('settlement_id')->nullable()->index();
            $table->string('invoice_type', 40)->index();
            $table->string('series', 20)->nullable();
            $table->string('folio', 40)->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 40)->default('pending')->index();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('stamped_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('commerce_invoices');
        Schema::connection($this->connection)->dropIfExists('commerce_tax_profiles');
        Schema::connection($this->connection)->dropIfExists('commerce_bank_accounts');
        Schema::connection($this->connection)->dropIfExists('finance_disputes');
        Schema::connection($this->connection)->dropIfExists('finance_refunds');
        Schema::connection($this->connection)->dropIfExists('settlement_items');
        Schema::connection($this->connection)->dropIfExists('settlements');
        Schema::connection($this->connection)->dropIfExists('finance_movements');
        Schema::connection($this->connection)->dropIfExists('payment_transactions');
    }
};
