<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Usuario;

class VerifyGeolocationSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolocation:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar la configuración del sistema de geolocalización en PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando configuración de geolocalización...');
        $this->newLine();

        // 1. Verificar conexión a PostgreSQL
        $this->checkDatabaseConnection();

        // 2. Verificar schema
        $this->checkSchema();

        // 3. Verificar tabla usuarios
        $this->checkTable();

        // 4. Verificar columnas de ubicación
        $this->checkColumns();

        // 5. Verificar índices
        $this->checkIndexes();

        // 6. Verificar datos de prueba
        $this->checkData();

        // 7. Resumen final
        $this->showSummary();

        return Command::SUCCESS;
    }

    protected function checkDatabaseConnection()
    {
        $this->info('1️⃣ Verificando conexión a PostgreSQL...');

        try {
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();

            if ($driver === 'pgsql') {
                $this->line("   ✅ Conectado a PostgreSQL");
                $this->line("   📊 Base de datos: <fg=cyan>{$database}</>");
                
                $version = DB::select("SELECT version()")[0]->version;
                $this->line("   🐘 Versión: <fg=gray>" . substr($version, 0, 50) . "...</>");
            } else {
                $this->error("   ❌ No estás usando PostgreSQL (driver actual: {$driver})");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error de conexión: " . $e->getMessage());
            return false;
        }

        $this->newLine();
        return true;
    }

    protected function checkSchema()
    {
        $this->info('2️⃣ Verificando schema...');

        try {
            $schema = config('database.connections.pgsql.search_path', 'public');
            $this->line("   📁 Schema configurado: <fg=cyan>{$schema}</>");

            // Verificar que el schema existe
            $schemaExists = DB::select(
                "SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?",
                [$schema]
            );

            if (count($schemaExists) > 0) {
                $this->line("   ✅ Schema existe en la base de datos");
            } else {
                $this->warn("   ⚠️  Schema '{$schema}' no encontrado");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
    }

    protected function checkTable()
    {
        $this->info('3️⃣ Verificando tabla usuarios...');

        try {
            if (Schema::hasTable('usuarios')) {
                $this->line("   ✅ Tabla 'usuarios' existe");

                $count = Usuario::count();
                $this->line("   📊 Total de registros: <fg=cyan>{$count}</>");
            } else {
                $this->error("   ❌ Tabla 'usuarios' no encontrada");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
    }

    protected function checkColumns()
    {
        $this->info('4️⃣ Verificando columnas de ubicación...');

        $requiredColumns = [
            'latitud' => 'numeric',
            'longitud' => 'numeric',
            'ciudad' => 'character varying',
            'estado' => 'character varying',
            'pais' => 'character varying',
            'ubicacion_capturada_at' => 'timestamp without time zone'
        ];

        $schema = config('database.connections.pgsql.search_path', 'public');
        $allFound = true;

        foreach ($requiredColumns as $column => $expectedType) {
            try {
                $result = DB::select(
                    "SELECT 
                        column_name, 
                        data_type,
                        is_nullable,
                        column_default
                    FROM information_schema.columns 
                    WHERE table_schema = ? 
                    AND table_name = 'usuarios' 
                    AND column_name = ?",
                    [$schema, $column]
                );

                if (count($result) > 0) {
                    $col = $result[0];
                    $nullable = $col->is_nullable === 'YES' ? '✓ nullable' : '✗ not null';
                    $default = $col->column_default ? " (default: {$col->column_default})" : '';
                    
                    $this->line("   ✅ <fg=green>{$column}</> | <fg=gray>{$col->data_type}</> | {$nullable}{$default}");
                } else {
                    $this->error("   ❌ Columna '{$column}' no encontrada");
                    $allFound = false;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error verificando '{$column}': " . $e->getMessage());
                $allFound = false;
            }
        }

        if ($allFound) {
            $this->line("   🎉 Todas las columnas están presentes");
        }

        $this->newLine();
    }

    protected function checkIndexes()
    {
        $this->info('5️⃣ Verificando índices...');

        $schema = config('database.connections.pgsql.search_path', 'public');

        $expectedIndexes = [
            'idx_usuarios_ubicacion',
            'idx_usuarios_pais',
            'idx_usuarios_ubicacion_fecha'
        ];

        foreach ($expectedIndexes as $indexName) {
            try {
                $result = DB::select(
                    "SELECT 
                        indexname,
                        indexdef
                    FROM pg_indexes 
                    WHERE schemaname = ? 
                    AND tablename = 'usuarios' 
                    AND indexname = ?",
                    [$schema, $indexName]
                );

                if (count($result) > 0) {
                    $this->line("   ✅ Índice '<fg=green>{$indexName}</>' existe");
                } else {
                    $this->warn("   ⚠️  Índice '{$indexName}' no encontrado");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
            }
        }

        $this->newLine();
    }

    protected function checkData()
    {
        $this->info('6️⃣ Verificando datos de ubicación...');

        try {
            $totalUsuarios = Usuario::count();
            $conUbicacion = Usuario::whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->count();
            $sinUbicacion = $totalUsuarios - $conUbicacion;

            if ($totalUsuarios > 0) {
                $porcentaje = round(($conUbicacion / $totalUsuarios) * 100, 2);
                
                $this->line("   📊 Total usuarios: <fg=cyan>{$totalUsuarios}</>");
                $this->line("   ✅ Con ubicación: <fg=green>{$conUbicacion}</> ({$porcentaje}%)");
                $this->line("   ⚪ Sin ubicación: <fg=yellow>{$sinUbicacion}</>");

                if ($conUbicacion > 0) {
                    // Mostrar ciudades más comunes
                    $topCiudades = DB::table('usuarios')
                        ->select('ciudad', 'estado', DB::raw('COUNT(*) as total'))
                        ->whereNotNull('ciudad')
                        ->groupBy('ciudad', 'estado')
                        ->orderByDesc('total')
                        ->limit(5)
                        ->get();

                    if ($topCiudades->count() > 0) {
                        $this->newLine();
                        $this->line("   🏙️  Top 5 ciudades:");
                        foreach ($topCiudades as $ciudad) {
                            $this->line("      • {$ciudad->ciudad}, {$ciudad->estado} ({$ciudad->total} usuarios)");
                        }
                    }

                    // Usuarios recientes con ubicación
                    $recientes = Usuario::whereNotNull('ubicacion_capturada_at')
                        ->where('ubicacion_capturada_at', '>=', now()->subDays(7))
                        ->count();

                    $this->newLine();
                    $this->line("   📅 Ubicaciones capturadas (últimos 7 días): <fg=cyan>{$recientes}</>");
                }
            } else {
                $this->warn("   ⚠️  No hay usuarios en la base de datos");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
    }

    protected function showSummary()
    {
        $this->info('📋 RESUMEN');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        try {
            $schema = config('database.connections.pgsql.search_path', 'public');
            
            // Verificar si todo está configurado correctamente
            $columnsExist = Schema::hasColumns('usuarios', [
                'latitud', 'longitud', 'ciudad', 'estado', 'pais', 'ubicacion_capturada_at'
            ]);

            if ($columnsExist) {
                $this->line('   ✅ <fg=green>Sistema de geolocalización configurado correctamente</>');
                $this->newLine();
                $this->line('   🎯 Próximos pasos:');
                $this->line('      1. Abre tu aplicación en el navegador');
                $this->line('      2. Permite el acceso a la ubicación cuando se solicite');
                $this->line('      3. Verifica que los datos se guarden en la tabla usuarios');
                $this->newLine();
                $this->line('   📝 Comandos útiles:');
                $this->line('      • <fg=cyan>php artisan tinker</> - Acceder a la consola interactiva');
                $this->line('      • <fg=cyan>Usuario::whereNotNull(\'latitud\')->get()</> - Ver usuarios con ubicación');
                $this->line('      • Consulta el archivo <fg=cyan>GEOLOCATION_POSTGRES.md</> para más información');
            } else {
                $this->error('   ❌ Sistema de geolocalización NO está configurado correctamente');
                $this->newLine();
                $this->line('   🔧 Ejecuta la migración:');
                $this->line('      <fg=cyan>php artisan migrate</>');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error en verificación: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
