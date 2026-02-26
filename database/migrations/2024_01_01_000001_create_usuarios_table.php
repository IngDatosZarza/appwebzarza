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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno'); // Ahora obligatorio
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // Para verificación de email
            $table->string('password');
            $table->string('telefono', 15)->unique(); // Obligatorio y único (formato: +52 + 10 dígitos)
            $table->date('fecha_nacimiento'); // Obligatorio, validar mayoría de edad
            $table->string('rfc', 13)->unique(); // Obligatorio y único (10 o 13 caracteres)
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable();
            $table->enum('rol', ['cliente', 'admin'])->default('cliente');
            $table->boolean('club_zarza')->default(true); // Flag para integración con Oppen
            $table->string('oppen_customer_id')->nullable()->unique(); // ID del cliente en Oppen si existe
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};