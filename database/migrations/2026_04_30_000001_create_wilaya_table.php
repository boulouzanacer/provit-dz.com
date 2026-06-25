<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilaya', function (Blueprint $table) {
            $table->increments('ID_WILAYA');
            $table->string('WILAYA', 100);
            $table->string('WILAYA2', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilaya');
    }
};
