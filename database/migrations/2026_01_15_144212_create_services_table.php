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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps(); // Added timestamps for good measure, though not strictly required if model disables them. Let's stick to plan but adding timestamps is usually good for catalogs. Actually, previous file didn't have them in migration but model had $timestamps = false. Let's keep it simple and just remove customer_id.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
