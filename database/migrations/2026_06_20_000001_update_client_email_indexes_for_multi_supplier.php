<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Unique email is kept for Pro-Vit customers.
    }

    public function down(): void
    {
        // No-op.
    }
};
