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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Foreign key to clients table
            $table->decimal('amount', 10, 2); // Amount of the invoice
            $table->decimal('total_amount_with_tva', 10, 2); // Amount of the invoice
            $table->decimal('amount_tva', 10, 2); // Amount of the tva
            $table->string('status'); // Status of the invoice (e.g., paid, pending, canceled)
            $table->date('due_date'); // Due date for payment
            $table->date('checkDate')->nullable();
            $table->string('factor_code')->unique();
            $table->string('factor_bar_code')->nullable();
     //       $table->decimal('final_price', 10, 2)->default(0);
            $table->decimal('remaining_price', 10, 2)->default(0);
            $table->enum('payment_type', ['check', 'cash'])->nullable();
            $table->integer('tva')->default(0); // Add TVA field
            $table->string('amount_in_words_en');
            $table->string('amount_in_words_fr');
            $table->string('amount_in_words_ar');
            $table->timestamps(); // Created at and updated at timestamps


        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
