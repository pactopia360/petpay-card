<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_comercio';

    public function up(): void
    {
        Schema::connection($this->connection)->table('commerce_identity_profiles', function (Blueprint $table): void {
            if (! Schema::connection($this->connection)->hasColumn('commerce_identity_profiles', 'incorporation_date')) {
                $table->date('incorporation_date')->nullable()->after('notarial_deed_number');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('commerce_identity_profiles', function (Blueprint $table): void {
            if (Schema::connection($this->connection)->hasColumn('commerce_identity_profiles', 'incorporation_date')) {
                $table->dropColumn('incorporation_date');
            }
        });
    }
};
