<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_orders';

    public function up(): void
    {
        Schema::connection($this->connection)->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('folio', 40)->unique();
            $table->unsignedBigInteger('commerce_user_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_name', 180)->nullable();
            $table->string('customer_email', 180)->nullable();
            $table->string('delivery_type', 30)->default('delivery')->index();
            $table->json('delivery_address')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('delivery_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->string('status', 40)->default('pending')->index();
            $table->string('payment_status', 40)->default('pending')->index();
            $table->timestamp('placed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('sku', 100)->nullable();
            $table->string('name', 200);
            $table->decimal('quantity', 14, 3)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection($this->connection)->create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('status', 40)->index();
            $table->string('source', 40)->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('order_status_histories');
        Schema::connection($this->connection)->dropIfExists('order_items');
        Schema::connection($this->connection)->dropIfExists('orders');
    }
};
