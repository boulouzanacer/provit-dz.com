<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cmd2', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cmd');
            $table->unsignedBigInteger('id_produit');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('sous_total', 12, 2);
            $table->timestamps();

            $table->foreign('id_cmd')->references('id')->on('cmd1')->onDelete('cascade');
            $table->foreign('id_produit')->references('id')->on('produit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cmd2');
    }
};
