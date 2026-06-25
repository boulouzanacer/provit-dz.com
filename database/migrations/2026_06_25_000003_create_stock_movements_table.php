<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_frs');
            $table->unsignedBigInteger('id_produit');
            $table->unsignedBigInteger('id_cmd')->nullable();
            $table->integer('quantity');
            $table->string('movement_type', 50);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('id_frs')->references('id')->on('frs')->onDelete('cascade');
            $table->foreign('id_produit')->references('id')->on('produit')->onDelete('cascade');
            $table->foreign('id_cmd')->references('id')->on('cmd1')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
