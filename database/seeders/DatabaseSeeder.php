<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\Cupon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $admin = Usuario::create([
            'nombres' => 'Admin',
            'apellido_paterno' => 'Sistema',
            'apellido_materno' => 'Principal',
            'email' => 'admin@puntosfidelidad.com',
            'password' => Hash::make('password123'),
            'telefone' => '555-0001',
            'genero' => 'masculino',
            'rol' => 'admin',
        ]);

        // Crear usuario cliente de ejemplo
        $cliente = Usuario::create([
            'nombres' => 'Juan Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'González',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password123'),
            'telefono' => '555-0002',
            'fecha_nacimiento' => '1990-05-15',
            'genero' => 'masculino',
            'rol' => 'cliente',
        ]);

        // Crear sucursales de ejemplo
        $sucursales = [
            [
                'codigo' => 'SUC001',
                'nombre' => 'Sucursal Centro',
                'direccion' => 'Av. Juárez 123, Centro, Ciudad de México',
                'telefono' => '555-1001',
                'actualizado_por' => $admin->id,
            ],
            [
                'codigo' => 'SUC002',
                'nombre' => 'Sucursal Norte',
                'direccion' => 'Blvd. Norte 456, Colonia Norte, Ciudad de México',
                'telefono' => '555-1002',
                'actualizado_por' => $admin->id,
            ],
            [
                'codigo' => 'SUC003',
                'nombre' => 'Sucursal Sur',
                'direccion' => 'Calzada del Sur 789, Colonia Sur, Ciudad de México',
                'telefono' => '555-1003',
                'actualizado_por' => $admin->id,
            ],
        ];

        foreach ($sucursales as $sucursal) {
            Sucursal::create($sucursal);
        }

        // Crear cupones de ejemplo
        $cupones = [
            [
                'nombre' => 'Descuento 10%',
                'descripcion' => 'Obtén un 10% de descuento en tu próxima compra',
                'puntos_requeridos' => 100,
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addMonths(3)->toDateString(),
                'activo' => true,
                'actualizado_por' => $admin->id,
            ],
            [
                'nombre' => 'Producto Gratis',
                'descripcion' => 'Obtén un producto gratis de la selección especial',
                'puntos_requeridos' => 500,
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addMonths(6)->toDateString(),
                'activo' => true,
                'actualizado_por' => $admin->id,
            ],
            [
                'nombre' => 'Descuento 25%',
                'descripcion' => 'Descuento del 25% en toda la tienda',
                'puntos_requeridos' => 1000,
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addYear()->toDateString(),
                'activo' => true,
                'actualizado_por' => $admin->id,
            ],
        ];

        foreach ($cupones as $cupon) {
            Cupon::create($cupon);
        }

        // Crear dirección para el cliente
        $cliente->direcciones()->create([
            'calle' => 'Calle Ejemplo',
            'numero' => '123',
            'colonia' => 'Colonia Centro',
            'codigo_postal' => '12345',
            'estado' => 'Ciudad de México',
            'ciudad' => 'Ciudad de México',
            'pais' => 'México',
            'referencias' => 'Casa azul con portón blanco',
            'tipo' => 'casa',
            'principal' => true,
            'actualizado_por' => $admin->id,
        ]);

        // Crear saldo de puntos para el cliente
        $cliente->puntos()->create([
            'saldo' => 0,
            'actualizado_por' => $admin->id,
        ]);

        $this->command->info('Datos de ejemplo creados exitosamente:');
        $this->command->info('- Admin: admin@puntosfidelidad.com / password123');
        $this->command->info('- Cliente: cliente@example.com / password123');
        $this->command->info('- 3 sucursales creadas');
        $this->command->info('- 3 cupones de ejemplo creados');
    }
}
