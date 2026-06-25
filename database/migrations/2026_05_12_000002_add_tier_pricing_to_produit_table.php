<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->boolean('enable_tier_pricing')->default(false)->after('abonne_only');
        });

        Schema::create('produit_quantity_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_produit');
            $table->unsignedInteger('quantity_min');
            $table->unsignedInteger('quantity_max')->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('id_produit')->references('id')->on('produit')->onDelete('cascade');
            $table->index(['id_produit', 'quantity_min']);
            $table->index(['id_produit', 'quantity_max']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_quantity_prices');

        Schema::table('produit', function (Blueprint $table) {
            $table->dropColumn('enable_tier_pricing');
        });
    }
};
