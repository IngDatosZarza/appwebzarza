<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorregirEncodingRapido extends Command
{
    protected $signature = 'db:corregir-encoding-rapido {--dry-run : Simular sin hacer cambios}';
    protected $description = 'Corregir encoding usando SQL bulk updates (más rápido)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info($dryRun ? '=== MODO SIMULACIÓN (--dry-run) ===' : '=== CORRECCIÓN RÁPIDA DE ENCODING ===');
        $this->newLine();

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }
            
            // Mapeo de reemplazos
            $reemplazos = [
                ['M?XICO', 'MÉXICO'],
                ['MICHOAC?N', 'MICHOACÁN'],
                ['QUER?TARO', 'QUERÉTARO'],
                ['YUCAT?N', 'YUCATÁN'],
                ['LE?N', 'LEÓN'],
                ['C?RDOBA', 'CÓRDOBA'],
                ['TORRE?N', 'TORREÓN'],
                ['G?MEZ', 'GÓMEZ'],
                ['POTOS?', 'POTOSÍ'],
                ['MART?NEZ', 'MARTÍNEZ'],
                ['RAM?REZ', 'RAMÍREZ'],
                ['G?LVEZ', 'GÁLVEZ'],
                ['JU?REZ', 'JUÁREZ'],
                ['L?PEZ', 'LÓPEZ'],
                ['P?REZ', 'PÉREZ'],
                ['HERN?NDEZ', 'HERNÁNDEZ'],
                ['S?NCHEZ', 'SÁNCHEZ'],
                ['DOM?NGUEZ', 'DOMÍNGUEZ'],
                ['GUTI?RREZ', 'GUTIÉRREZ'],
                ['RODR?GUEZ', 'RODRÍGUEZ'],
                ['GONZ?LEZ', 'GONZÁLEZ'],
                ['COYOAC?N', 'COYOACÁN'],
                ['TULTITL?N', 'TULTITLÁN'],
                ['TLANEPANTLA', 'TLALNEPANTLA'], // Común
                ['BENITO JU?REZ', 'BENITO JUÁREZ'],
                ['?TICA', 'ÁTICA'],
                ['?GUILA', 'ÁGUILA'],
                ['?NGEL', 'ÁNGEL'],
                ['?REA', 'ÁREA'],
                ['?RBOL', 'ÁRBOL'],
                ['Á', 'É'], // Reemplazo genérico final
            ];
            
            $totalActualizaciones = 0;
            
            foreach (['estado', 'municipio', 'colonia'] as $columna) {
                $this->info("Procesando columna: $columna");
                
                foreach ($reemplazos as $reemplazo) {
                    [$buscar, $reemplazar] = $reemplazo;
                    
                    if ($dryRun) {
                        $count = DB::table('codigos_postales')
                            ->where($columna, 'LIKE', '%' . $buscar . '%')
                            ->count();
                        
                        if ($count > 0) {
                            $this->line("  - Reemplazar '$buscar' → '$reemplazar': $count registros");
                            $totalActualizaciones += $count;
                        }
                    } else {
                        $updated = DB::table('codigos_postales')
                            ->where($columna, 'LIKE', '%' . $buscar . '%')
                            ->update([
                                $columna => DB::raw("REPLACE($columna, '$buscar', '$reemplazar')")
                            ]);
                        
                        if ($updated > 0) {
                            $this->line("  - Reemplazado '$buscar' → '$reemplazar': $updated registros");
                            $totalActualizaciones += $updated;
                        }
                    }
                }
            }
            
            $this->newLine();
            
            if ($dryRun) {
                $this->info("SIMULACIÓN: Se actualizarían aproximadamente $totalActualizaciones registros");
                $this->line("Ejecute sin --dry-run para aplicar los cambios");
            } else {
                DB::commit();
                $this->success("✓ Proceso completado. Registros actualizados: $totalActualizaciones");
            }
            
            // Verificar cuántos quedan con problemas
            $this->newLine();
            $this->info('Verificando registros restantes con problemas...');
            $restantes = DB::table('codigos_postales')
                ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
                ->count();
            
            $this->line("Registros que aún tienen '?': " . number_format($restantes));
            
            if ($restantes > 0 && !$dryRun) {
                $this->warn("Nota: Hay $restantes registros que requieren corrección manual o patrones adicionales");
                
                // Mostrar ejemplos
                $this->newLine();
                $this->info('Ejemplos de textos restantes:');
                $ejemplos = DB::table('codigos_postales')
                    ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
                    ->select(DB::raw('DISTINCT COALESCE(estado, municipio, colonia) as texto'))
                    ->limit(10)
                    ->get();
                
                foreach ($ejemplos as $ej) {
                    if (strpos($ej->texto, '?') !== false) {
                        $this->line('  - ' . $ej->texto);
                    }
                }
            } elseif ($restantes == 0 && !$dryRun) {
                $this->success('¡Todos los caracteres corruptos fueron corregidos!');
            }
            
        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
