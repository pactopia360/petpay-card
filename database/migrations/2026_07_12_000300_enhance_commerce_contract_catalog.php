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
            if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', 'template_key')) {
                $table->string('template_key', 100)->nullable()->after('branch_id')->index();
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', 'group_key')) {
                $table->string('group_key', 100)->default('corporate')->after('template_key')->index();
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', 'is_required')) {
                $table->boolean('is_required')->default(true)->after('group_key');
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', 'document_year')) {
                $table->unsignedSmallInteger('document_year')->nullable()->after('version')->index();
            }

            if (! Schema::connection($this->connection)->hasColumn('commerce_contracts', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('document_year');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('commerce_contracts', function (Blueprint $table): void {
            foreach (['template_key', 'group_key', 'is_required', 'document_year', 'sort_order'] as $column) {
                if (Schema::connection($this->connection)->hasColumn('commerce_contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};