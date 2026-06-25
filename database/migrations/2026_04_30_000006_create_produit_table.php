<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit', function (Blueprint $table) {
            $table->id();
            $table->string('code_barre', 120)->nullable()->unique();
            $table->string('designation', 255);
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('image_principale', 500)->nullable();
            $table->unsignedBigInteger('id_category')->nullable();
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit');
    }
};
