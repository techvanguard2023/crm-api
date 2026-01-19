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
        Schema::create('service_renewals', function (Blueprint $table) {
            $table->id();
            // We reference the pivot table ID. 
            // Ensure CustomerService model uses incrementing ID or we reference the composite key.
            // CustomerService migration has $table->id(); so we are good.
            $table->foreignId('customer_service_id')->constrained('customer_service')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2);
            $table->date('renewed_at'); // Date of payment/renewal
            $table->date('renews_until'); // The new due date generated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_renewals');
    }
};
