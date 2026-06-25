<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->decimal('pv_1', 10, 2)->default(0)->after('description');
            $table->decimal('pv_2', 10, 2)->default(0)->after('pv_1');
            $table->decimal('pv_3', 10, 2)->default(0)->after('pv_2');
            $table->tinyInteger('abonne_only')->default(0)->after('id_category');
        });

        if (Schema::hasColumn('produit', 'prix')) {
            DB::table('produit')->update([
                'pv_1' => DB::raw('prix'),
                'pv_2' => DB::raw('prix'),
                'pv_3' => DB::raw('prix'),
            ]);

            Schema::table('produit', function (Blueprint $table) {
                $table->dropColumn('prix');
            });
        }
    }

    public function down(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->decimal('prix', 10, 2)->default(0)->after('description');
        });

        DB::table('produit')->update([
            'prix' => DB::raw('pv_1'),
        ]);

        Schema::table('produit', function (Blueprint $table) {
            $table->dropColumn(['pv_1', 'pv_2', 'pv_3', 'abonne_only']);
        });
    }
};
