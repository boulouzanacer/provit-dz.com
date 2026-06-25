<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
        });

        Schema::table('produit', function (Blueprint $table) {
            $table->foreign('id_category')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->dropForeign(['id_category']);
        });

        Schema::dropIfExists('categories');
    }
};
