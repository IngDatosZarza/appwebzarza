<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('direcciones', function (Blueprint $table) {
            // Hacer calle y numero opcionales (nullable)
            $table->string('calle')->nullable()->change();
            $table->string('numero')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direcciones', function (Blueprint $table) {
            // Revertir: hacer calle y numero obligatorios de nuevo
            $table->string('calle')->nullable(false)->change();
            $table->string('numero')->nullable(false)->change();
        });
    }
};
