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
        Schema::table('usuarios', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->string('rfc', 13)->unique()->nullable()->after('fecha_nacimiento');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->boolean('club_zarza')->default(true)->after('rol');
            $table->string('oppen_customer_id')->nullable()->unique()->after('club_zarza');
            
            // Modificar campos existentes para que sean NOT NULL
            // Nota: Solo en producción, primero llenar con valores por defecto
        });
        
        // Agregar índices únicos a telefono si no existen
        try {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->unique('telefono');
            });
        } catch (\Exception $e) {
            // Ya existe, ignorar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['rfc', 'email_verified_at', 'club_zarza', 'oppen_customer_id']);
            $table->dropUnique(['telefono']);
        });
    }
};
