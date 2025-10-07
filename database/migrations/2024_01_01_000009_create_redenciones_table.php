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
        Schema::create('redenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cupon_asignado_id')->unique()->constrained('cupones_asignados')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamp('fecha_redencion');
            $table->text('observaciones')->nullable();
            $table->foreignId('realizado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redenciones');
    }
};