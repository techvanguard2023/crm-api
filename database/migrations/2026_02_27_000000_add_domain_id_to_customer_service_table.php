<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_service', function (Blueprint $table) {
            $table->foreignId('domain_id')
                ->nullable()
                ->after('service_id')
                ->constrained('domains')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_service', function (Blueprint $table) {
            $table->dropConstrainedForeignId('domain_id');
        });
    }
};
