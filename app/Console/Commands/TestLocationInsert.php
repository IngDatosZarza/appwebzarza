<?php

namespace App\Console\Commands;

use App\Models\UbicacionUsuario;
use Illuminate\Console\Command;

class TestLocationInsert extends Command
{
    protected $signature = 'location:test-insert';
    protected $description = 'Insertar una ubicación de prueba en la base de datos';

    public function handle()
    {
        $this->info('🧪 Iniciando test de inserción de ubicación...');
        $this->newLine();

        try {
            // Datos de prueba
            $testData = [
                'usuario_id' => null, // Visitante anónimo
                'latitud' => 20.6736,
                'longitud' => -103.3440,
                'precision' => 10.5,
                'ciudad' => 'Guadalajara',
                'estado' => 'Jalisco',
                'pais' => 'México',
                'codigo_postal' => '44100',
                'dispositivo' => 'mobile',
                'navegador' => 'Chrome',
                'sistema_operativo' => 'Android',
                'user_agent' => 'Mozilla/5.0 (Linux; Android 10)',
                'ip_address' => '192.168.1.1',
                'pagina_origen' => '/test',
                'evento' => 'test',
                'session_id' => 'test-session-' . time(),
                'es_primera_visita' => true,
                'metadata' => ['test' => true, 'source' => 'artisan'],
            ];

            $this->info('Datos a insertar:');
            $this->table(
                ['Campo', 'Valor'],
                collect($testData)->map(function ($value, $key) {
                    return [$key, is_string($value) || is_numeric($value) ? $value : json_encode($value)];
                })->toArray()
            );

            $this->newLine();
            $this->info('Insertando en la base de datos...');

            $ubicacion = UbicacionUsuario::create($testData);

            $this->newLine();
            $this->info('✅ ¡Ubicación insertada exitosamente!');
            $this->newLine();

            $this->info('Detalles del registro creado:');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $ubicacion->id],
                    ['Latitud', $ubicacion->latitud],
                    ['Longitud', $ubicacion->longitud],
                    ['Ciudad', $ubicacion->ciudad],
                    ['Estado', $ubicacion->estado],
                    ['País', $ubicacion->pais],
                    ['Dispositivo', $ubicacion->dispositivo],
                    ['Evento', $ubicacion->evento],
                    ['Primera visita', $ubicacion->es_primera_visita ? 'Sí' : 'No'],
                    ['Creado en', $ubicacion->created_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->newLine();
            $this->info('📊 Estadísticas generales:');
            $stats = UbicacionUsuario::estadisticas();
            
            $this->table(
                ['Métrica', 'Valor'],
                collect($stats)->map(function ($value, $key) {
                    return [ucfirst(str_replace('_', ' ', $key)), $value];
                })->toArray()
            );

            $this->newLine();
            $this->info('✅ Test completado exitosamente');
            $this->info('💡 Puedes verificar en la base de datos:');
            $this->line("   SELECT * FROM appweb.ubicaciones_usuarios WHERE id = {$ubicacion->id};");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Error al insertar ubicación:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
