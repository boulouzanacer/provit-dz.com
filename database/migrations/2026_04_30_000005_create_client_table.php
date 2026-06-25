<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client', function (Blueprint $table) {
            $table->id();
            $table->string('code_client', 50)->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->unsignedInteger('id_wilaya')->nullable();
            $table->unsignedInteger('id_commune')->nullable();
            $table->enum('type_client', ['simple'])->default('simple');
            $table->unsignedBigInteger('id_frs')->nullable();
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_wilaya')->references('ID_WILAYA')->on('wilaya');
            $table->foreign('id_commune')->references('ID_COMMUNE')->on('commune');
            $table->foreign('id_frs')->references('id')->on('frs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
