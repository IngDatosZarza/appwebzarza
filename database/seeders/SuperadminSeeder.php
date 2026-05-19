<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperadminSeeder extends Seeder
{
    /**
     * Crear el superadmin inicial.
     * 
     * Ejecutar con: php artisan db:seed --class=SuperadminSeeder
     */
    public function run(): void
    {
        $email = 'admin@test.com';
        
        // Verificar si ya existe
        $existente = DB::table('administradores')->where('email', $email)->first();
        
        if ($existente) {
            $this->command->info("⚠️  El superadmin {$email} ya existe.");
            
            // Preguntar si desea resetear la contraseña
            if ($this->command->confirm('¿Deseas resetear la contraseña a SuperAdmin2026! ?', false)) {
                DB::statement("
                    UPDATE administradores 
                    SET password = ?,
                        intentos_fallidos = 0,
                        bloqueado_hasta = NULL,
                        activo = TRUE,
                        updated_at = NOW()
                    WHERE email = ?
                ", [Hash::make('SuperAdmin2026!'), $email]);
                
                $this->command->info("✅ Contraseña reseteada exitosamente.");
                $this->command->info("📧 Email: {$email}");
                $this->command->info("🔑 Password: SuperAdmin2026!");
            }
            
            return;
        }
        
        // Crear el superadmin usando DB::statement para evitar problemas con booleanos
        DB::statement("
            INSERT INTO administradores (
                nombres, apellido_paterno, apellido_materno, email, password, 
                telefono, rol, activo, intentos_fallidos, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, 0, NOW(), NOW())
        ", [
            'Admin',
            'Sistema',
            'Principal',
            $email,
            Hash::make('SuperAdmin2026!'),
            '+525500000001',
            'superadmin'
        ]);
        
        $this->command->info("✅ Superadmin creado exitosamente!");
        $this->command->line("");
        $this->command->info("=== CREDENCIALES DE ACCESO ===");
        $this->command->info("📧 Email: {$email}");
        $this->command->info("🔑 Password: SuperAdmin2026!");
        $this->command->info("🌐 URL: /admin/login");
        $this->command->line("");
        $this->command->warn("⚠️  IMPORTANTE: Cambia la contraseña después del primer login.");
    }
}
