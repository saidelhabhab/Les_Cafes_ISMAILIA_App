<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('quantity', 8, 2);
            $table->string('unit');
            $table->date('purchase_date');
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('total_ht', 10, 2)->nullable(); // Nouveau champ pour le Total HT
            $table->decimal('price_tva', 10, 2)->nullable(); // Nouveau champ pour la TVA
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
