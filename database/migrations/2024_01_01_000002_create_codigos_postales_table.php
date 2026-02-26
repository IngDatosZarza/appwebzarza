<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para almacenar catálogo de códigos postales de México
     * Fuente: https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx
     */
    public function up(): void
    {
        Schema::create('codigos_postales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_postal', 5)->index();
            $table->string('estado', 100)->index();
            $table->string('municipio', 100)->index();
            $table->string('ciudad', 100)->nullable();
            $table->string('colonia', 200);
            $table->string('tipo_asentamiento', 50)->nullable();
            $table->string('zona', 50)->nullable(); // Urbano, Rural, etc.
            
            // Índices compuestos para búsquedas eficientes
            $table->index(['estado', 'municipio']);
            $table->index(['codigo_postal', 'colonia']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_postales');
    }
};
