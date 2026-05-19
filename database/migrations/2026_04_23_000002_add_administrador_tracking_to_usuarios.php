<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('registrado_por_administrador_id')
                ->nullable()
                ->after('registrado_por_admin_id')
                ->constrained('administradores')
                ->onDelete('set null');

            $table->foreignId('sucursal_registro_id')
                ->nullable()
                ->after('registrado_por_administrador_id')
                ->constrained('sucursales')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['registrado_por_administrador_id']);
            $table->dropColumn('registrado_por_administrador_id');
            $table->dropForeign(['sucursal_registro_id']);
            $table->dropColumn('sucursal_registro_id');
        });
    }
};
