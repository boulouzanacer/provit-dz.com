<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_frs');
            $table->unsignedBigInteger('id_produit');
            $table->integer('quantite')->default(0);
            $table->timestamps();

            $table->foreign('id_frs')->references('id')->on('frs')->onDelete('cascade');
            $table->foreign('id_produit')->references('id')->on('produit')->onDelete('cascade');
            $table->unique(['id_frs', 'id_produit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_stocks');
    }
};
