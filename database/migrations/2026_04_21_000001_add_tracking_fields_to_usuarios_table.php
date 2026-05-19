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
            // Campos para tracking del origen del registro
            $table->enum('origen_registro', ['autoregistro', 'admin_sucursal', 'campana'])->default('autoregistro')->after('oppen_customer_id');
            $table->string('dispositivo_registro', 20)->nullable()->after('origen_registro'); // mobile, desktop, tablet
            $table->foreignId('registrado_por_admin_id')->nullable()->constrained('usuarios')->onDelete('set null')->after('dispositivo_registro');
            $table->string('campana_id', 100)->nullable()->after('registrado_por_admin_id'); // ID de campaña marketing
            $table->text('user_agent')->nullable()->after('campana_id'); // Info del navegador
            $table->string('ip_registro', 45)->nullable()->after('user_agent'); // IPv4 o IPv6
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['registrado_por_admin_id']);
            $table->dropColumn([
                'origen_registro',
                'dispositivo_registro',
                'registrado_por_admin_id',
                'campana_id',
                'user_agent',
                'ip_registro'
            ]);
        });
    }
};
