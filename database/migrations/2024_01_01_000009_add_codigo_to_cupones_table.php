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
        if (!Schema::hasColumn('cupones', 'codigo')) {
            Schema::table('cupones', function (Blueprint $table) {
                // Agregar código único para el cupón (ej: BANDERILLAS20, PROMO50, etc)
                $table->string('codigo', 50)->unique()->after('nombre');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cupones', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
