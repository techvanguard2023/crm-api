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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_service_id')->constrained('customer_service')->onDelete('cascade');
            
            // Core identification
            $table->uuid('request_id')->unique()->nullable(); // codigoSolicitacao
            $table->string('your_number')->nullable(); // seuNumero
            
            // Financial details
            $table->decimal('amount', 10, 2); // valorTotalRecebido or initial value
            
            // Status and Date
            $table->string('status')->default('A_RECEBER'); // situacao
            $table->dateTime('paid_at')->nullable(); // dataHoraSituacao
            
            // Payment details
            $table->string('payment_method')->nullable(); // origemRecebimento
            
            // Bank details
            $table->string('our_number')->nullable(); // nossoNumero
            $table->text('barcode')->nullable(); // codigoBarras
            $table->text('digitable_line')->nullable(); // linhaDigitavel
            
            // PIX details
            $table->string('txid')->nullable(); // txid
            $table->text('pix_copy_paste')->nullable(); // pixCopiaECola

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
