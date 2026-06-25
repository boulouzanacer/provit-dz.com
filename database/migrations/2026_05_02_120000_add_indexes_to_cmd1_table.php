<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cmd1', function (Blueprint $table) {
            $table->index('id_client', 'cmd1_id_client_idx');
            $table->index('id_frs', 'cmd1_id_frs_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cmd1', function (Blueprint $table) {
            $table->dropIndex('cmd1_id_client_idx');
            $table->dropIndex('cmd1_id_frs_idx');
        });
    }
};

