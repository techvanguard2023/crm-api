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
            $table->date('start_date')->after('recurrence')->nullable(); // Nullable for existing records, though we should probably fill them.
            $table->date('next_due_date')->after('start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_service', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'next_due_date']);
        });
    }
};
