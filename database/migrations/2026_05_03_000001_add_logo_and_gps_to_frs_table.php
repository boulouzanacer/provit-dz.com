<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frs', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('telephone');
            $table->decimal('latitude', 10, 7)->nullable()->after('id_commune');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('frs', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'latitude', 'longitude']);
        });
    }
};

