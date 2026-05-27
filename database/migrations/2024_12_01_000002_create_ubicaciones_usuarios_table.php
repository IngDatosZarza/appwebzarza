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
        Schema::create('ubicaciones_usuarios', function (Blueprint $table) {
            $table->id();
            
            // Relación con usuario (nullable para visitantes anónimos)
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            
            // Coordenadas GPS
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->decimal('precision', 10, 2)->nullable()->comment('Precisión en metros');
            
            // Información geográfica (reverse geocoding)
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('pais', 100)->default('México');
            $table->string('codigo_postal', 10)->nullable();
            
            // Información del dispositivo y navegador (para marketing)
            $table->string('dispositivo', 50)->nullable()->comment('mobile, tablet, desktop');
            $table->string('navegador', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            
            // Contexto de captura
            $table->string('pagina_origen', 255)->nullable()->comment('URL donde se capturó');
            $table->string('evento', 100)->nullable()->comment('registro, compra, navegacion, etc.');
            $table->string('session_id', 255)->nullable()->comment('ID de sesión del navegador');
            
            // Metadata para análisis
            $table->boolean('es_primera_visita')->default(false);
            $table->json('metadata')->nullable()->comment('Datos adicionales JSON');
            
            $table->timestamps();
            
            // Índices para optimizar búsquedas de marketing
            $table->index('usuario_id');
            $table->index(['ciudad', 'estado']);
            $table->index('pais');
            $table->index('evento');
            $table->index('created_at');
            $table->index('session_id');
        });

        // Índice compuesto para análisis temporal por usuario
        Schema::table('ubicaciones_usuarios', function (Blueprint $table) {
            $table->index(['usuario_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_usuarios');
    }
};
