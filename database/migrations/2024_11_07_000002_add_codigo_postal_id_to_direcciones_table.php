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
            // Agregar nuevos campos
            $table->foreignId('codigo_postal_id')->nullable()->after('numero')->constrained('codigos_postales')->onDelete('restrict');
            $table->string('municipio', 100)->nullable()->after('estado');
            
            // Renombrar ciudad a municipio si es necesario
            // Por ahora solo agregamos municipio y mantenemos ciudad para compatibilidad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropForeign(['codigo_postal_id']);
            $table->dropColumn(['codigo_postal_id', 'municipio']);
        });
    }
};
