<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Usuario;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'qr_codigo')) {
                $table->string('qr_codigo', 64)->unique()->nullable()->after('oppen_customer_id');
            }
        });

        // Generar códigos QR para usuarios existentes que no tengan uno
        Usuario::whereNull('qr_codigo')->each(function ($usuario) {
            $usuario->update(['qr_codigo' => 'ZRZ-' . strtoupper(Str::random(16))]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'qr_codigo')) {
                $table->dropUnique(['qr_codigo']);
                $table->dropColumn('qr_codigo');
            }
        });
    }
};
