<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_produit');
            $table->string('filename', 255);
            $table->string('url_principale', 500);
            $table->string('url_thumbnail', 500);
            $table->tinyInteger('ordre')->default(0);
            $table->timestamps();

            $table->foreign('id_produit')->references('id')->on('produit')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_images');
    }
};
