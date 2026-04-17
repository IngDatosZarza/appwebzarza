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
            if (!Schema::hasColumn('direcciones', 'codigo_postal_id')) {
                $table->foreignId('codigo_postal_id')->nullable()->after('numero')->constrained('codigos_postales')->onDelete('restrict');
            }
            if (!Schema::hasColumn('direcciones', 'municipio')) {
                $table->string('municipio', 100)->nullable()->after('estado');
            }
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
