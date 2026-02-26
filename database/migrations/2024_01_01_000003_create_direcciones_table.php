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
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            // Datos de dirección - todos obligatorios
            $table->string('calle');
            $table->string('numero');
            $table->foreignId('codigo_postal_id')->constrained('codigos_postales')->onDelete('restrict');
            
            // Campos desnormalizados para consultas rápidas (se copian del catálogo)
            $table->string('codigo_postal', 5);
            $table->string('estado', 100);
            $table->string('municipio', 100);
            $table->string('colonia', 200);
            
            $table->string('pais')->default('México');
            $table->text('referencias')->nullable();
            $table->enum('tipo', ['casa', 'trabajo', 'otro'])->default('casa');
            $table->boolean('principal')->default(false);
            $table->foreignId('actualizado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index(['usuario_id', 'principal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};