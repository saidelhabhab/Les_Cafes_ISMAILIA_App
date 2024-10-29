<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnItemsTable extends Migration
{
    public function up()
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 4);
            $table->enum('unit', ['ton', 'kg', 'g']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('return_items');
    }
}
