<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promociones_oppen', function (Blueprint $table) {
            $table->id();
            $table->string('oppen_code', 50)->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->json('dias_semana')->nullable();
            $table->json('horarios')->nullable();
            $table->json('condiciones')->nullable();
            $table->json('acciones')->nullable();
            $table->boolean('combinable')->default(false);
            $table->json('datos_raw')->nullable();
            $table->timestamp('ultima_sincronizacion')->nullable();
            $table->timestamps();

            $table->index(['activo', 'fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones_oppen');
    }
};
