<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commune', function (Blueprint $table) {
            $table->increments('ID_COMMUNE');
            $table->string('COMMUNE', 100);
            $table->unsignedInteger('ID_WILAYA');

            $table->foreign('ID_WILAYA')->references('ID_WILAYA')->on('wilaya');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commune');
    }
};
