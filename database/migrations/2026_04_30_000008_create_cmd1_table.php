<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cmd1', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_client');
            $table->unsignedBigInteger('id_frs');
            $table->dateTime('date_cmd')->useCurrent();
            $table->enum('statut', ['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'])->default('en_attente');
            $table->decimal('montant_total', 12, 2);
            $table->text('adresse_livraison');
            $table->unsignedInteger('id_wilaya')->nullable();
            $table->unsignedInteger('id_commune')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('synced_pme')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_client')->references('id')->on('client');
            $table->foreign('id_frs')->references('id')->on('frs');
            $table->foreign('id_wilaya')->references('ID_WILAYA')->on('wilaya');
            $table->foreign('id_commune')->references('ID_COMMUNE')->on('commune');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cmd1');
    }
};
