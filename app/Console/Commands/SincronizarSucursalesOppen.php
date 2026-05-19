<?php

namespace App\Console\Commands;

use App\Models\Sucursal;
use App\Services\OppenApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarSucursalesOppen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oppen:sync-sucursales 
                            {--start=1 : Número inicial del rango (ej: 1 para LZ0001)}
                            {--end=200 : Número final del rango (ej: 200 para LZ0200)}
                            {--force : Actualizar sucursales existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar sucursales desde la API Oppen (códigos LZ****)';

    private OppenApiService $oppenService;

    public function __construct()
    {
        parent::__construct();
        $this->oppenService = new OppenApiService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = (int) $this->option('start');
        $end = (int) $this->option('end');
        $force = $this->option('force');

        $this->info("Sincronizando sucursales Oppen desde LZ{$this->formatCode($start)} hasta LZ{$this->formatCode($end)}...");
        $this->newLine();

        $creadas = 0;
        $actualizadas = 0;
        $omitidas = 0;
        $errores = 0;

        $bar = $this->output->createProgressBar($end - $start + 1);
        $bar->start();

        for ($i = $start; $i <= $end; $i++) {
            $codigo = 'LZ' . $this->formatCode($i);
            
            // Obtener sucursal desde Oppen
            $datosOppen = $this->oppenService->obtenerSucursal($codigo);
            
            if (!$datosOppen) {
                $bar->advance();
                continue; // No existe o está cerrada
            }

            try {
                $existente = Sucursal::where('codigo', $codigo)->first();

                if ($existente) {
                    if ($force) {
                        // Actualizar
                        DB::statement("
                            UPDATE sucursales 
                            SET nombre = ?,
                                direccion = ?,
                                telefono = ?,
                                updated_at = NOW()
                            WHERE codigo = ?
                        ", [
                            $datosOppen['Name'],
                            $datosOppen['Address'] ?? 'Sin dirección',
                            $datosOppen['Phone'] ?? null,
                            $codigo
                        ]);
                        $actualizadas++;
                    } else {
                        $omitidas++;
                    }
                } else {
                    // Crear nueva
                    DB::statement("
                        INSERT INTO sucursales (codigo, nombre, direccion, telefono, created_at, updated_at)
                        VALUES (?, ?, ?, ?, NOW(), NOW())
                    ", [
                        $codigo,
                        $datosOppen['Name'],
                        $datosOppen['Address'] ?? 'Sin dirección',
                        $datosOppen['Phone'] ?? null
                    ]);
                    $creadas++;
                }
            } catch (\Exception $e) {
                $this->error("\nError procesando {$codigo}: " . $e->getMessage());
                $errores++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumen
        $this->info("=== Resumen de Sincronización ===");
        $this->line("✅ Creadas: {$creadas}");
        $this->line("🔄 Actualizadas: {$actualizadas}");
        $this->line("⏭️  Omitidas (ya existen): {$omitidas}");
        $this->line("❌ Errores: {$errores}");
        $this->newLine();

        if ($omitidas > 0 && !$force) {
            $this->comment("💡 Usa --force para actualizar sucursales existentes");
        }

        return 0;
    }

    /**
     * Formatear número a 4 dígitos con ceros a la izquierda.
     */
    private function formatCode(int $numero): string
    {
        return str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}
