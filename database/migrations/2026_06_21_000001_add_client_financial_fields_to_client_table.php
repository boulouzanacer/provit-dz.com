<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->decimal('achat_client', 14, 2)->default(0)->after('tarif');
            $table->decimal('versement_client', 14, 2)->default(0)->after('achat_client');
            $table->decimal('solde_client', 14, 2)->default(0)->after('versement_client');
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn(['achat_client', 'versement_client', 'solde_client']);
        });
    }
};
