<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frs', function (Blueprint $table) {
            $table->id();
            $table->string('nom_frs');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->unsignedInteger('id_wilaya')->nullable();
            $table->unsignedInteger('id_commune')->nullable();
            $table->string('token', 255)->unique();
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_wilaya')->references('ID_WILAYA')->on('wilaya');
            $table->foreign('id_commune')->references('ID_COMMUNE')->on('commune');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frs');
    }
};
