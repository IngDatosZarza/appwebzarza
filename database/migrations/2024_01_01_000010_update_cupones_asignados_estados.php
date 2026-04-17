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
        // Solo ejecutar si el estado todavía tiene los valores antiguos
        if (Schema::hasColumn('cupones_asignados', 'estado') && !Schema::hasColumn('cupones_asignados', 'fecha_uso')) {
            Schema::table('cupones_asignados', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
            Schema::table('cupones_asignados', function (Blueprint $table) {
                $table->enum('estado', ['asignado', 'usado', 'vencido', 'bloqueado'])->default('asignado')->after('cupon_id');
                $table->timestamp('fecha_uso')->nullable()->after('codigo_qr');
                $table->foreignId('validado_por')->nullable()->constrained('usuarios')->onDelete('set null')->after('fecha_uso');
            });
        } elseif (!Schema::hasColumn('cupones_asignados', 'fecha_uso')) {
            Schema::table('cupones_asignados', function (Blueprint $table) {
                $table->timestamp('fecha_uso')->nullable()->after('codigo_qr');
                $table->foreignId('validado_por')->nullable()->constrained('usuarios')->onDelete('set null')->after('fecha_uso');
            });
        }
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
