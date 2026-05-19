<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('telefono', 15)->unique();
            $table->enum('rol', ['superadmin', 'admin_sucursal'])->default('admin_sucursal');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->unsignedSmallInteger('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->timestamps();

            $table->index('rol');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
