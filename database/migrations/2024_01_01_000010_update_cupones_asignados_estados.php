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
        Schema::table('cupones_asignados', function (Blueprint $table) {
            // Cambiar estados para incluir 'usado' y 'bloqueado'
            $table->dropColumn('estado');
        });
        
        Schema::table('cupones_asignados', function (Blueprint $table) {
            $table->enum('estado', ['asignado', 'usado', 'vencido', 'bloqueado'])->default('asignado')->after('cupon_id');
            
            // Agregar campos para tracking de uso del cupón
            $table->timestamp('fecha_uso')->nullable()->after('codigo_qr');
            $table->foreignId('validado_por')->nullable()->constrained('usuarios')->onDelete('set null')->after('fecha_uso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cupones_asignados', function (Blueprint $table) {
            $table->dropColumn(['fecha_uso', 'validado_por']);
            $table->dropColumn('estado');
        });
        
        Schema::table('cupones_asignados', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'redimido', 'vencido'])->default('pendiente')->after('cupon_id');
        });
    }
};
