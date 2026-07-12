<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->create('commerce_monetization_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name', 160);
            $table->string('slug', 190)->nullable();
            $table->enum('type', ['coupon', 'discount', 'sponsored', 'cashback', 'referral', 'membership'])->default('discount');
            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'finished', 'rejected'])->default('draft')->index();
            $table->enum('scope', ['all', 'branch', 'category', 'product'])->default('all');
            $table->decimal('budget', 14, 2)->default(0);
            $table->decimal('spent', 14, 2)->default(0);
            $table->decimal('discount_value', 14, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->string('coupon_code', 80)->nullable()->index();
            $table->decimal('minimum_purchase', 14, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('orders')->default(0);
            $table->decimal('attributed_sales', 14, 2)->default(0);
            $table->decimal('cashback_percentage', 8, 4)->nullable();
            $table->json('targeting')->nullable();
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->text('description')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commerce_user_id', 'status']);
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::connection($this->connection)->create('commerce_monetization_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->string('event_type', 60)->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reference', 190)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('commerce_monetization_events');
        Schema::connection($this->connection)->dropIfExists('commerce_monetization_campaigns');
    }
};