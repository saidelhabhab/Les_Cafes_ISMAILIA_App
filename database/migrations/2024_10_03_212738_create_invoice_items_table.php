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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // Foreign key to invoices table
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 4); // Quantity of the item
            $table->enum('unit', ['ton', 'kg', 'g']); 
            $table->decimal('price', 10, 2); // Price per unit of the item
            $table->decimal('total', 10, 2); // Total price for the line item (quantity * price)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
