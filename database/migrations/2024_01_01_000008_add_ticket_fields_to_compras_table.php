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
        Schema::table('compras', function (Blueprint $table) {
            $table->string('numero_ticket', 50)->unique()->nullable()->after('monto');
            $table->text('descripcion')->nullable()->after('puntos_generados');
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo')->after('descripcion');
            $table->timestamp('fecha_compra')->nullable()->after('metodo_pago');
            $table->index('numero_ticket');
            $table->index('fecha_compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['numero_ticket']);
            $table->dropIndex(['fecha_compra']);
            $table->dropColumn(['numero_ticket', 'descripcion', 'metodo_pago', 'fecha_compra']);
        });
    }
};