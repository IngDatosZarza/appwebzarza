<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorregirEncoding extends Command
{
    protected $signature = 'db:corregir-encoding {--dry-run : Simular sin hacer cambios} {--limit= : Limitar cantidad de registros} {--force : No pedir confirmación}';
    protected $description = 'Corregir problemas de encoding en codigos_postales';

    // Mapeo de caracteres corruptos a correctos
    protected $reemplazos = [
        '?' => 'É', // Más común
        'M?XICO' => 'MÉXICO',
        'MICHOAC?N' => 'MICHOACÁN',
        'QUER?TARO' => 'QUERÉTARO',
        'YUCAT?N' => 'YUCATÁN',
        'NUEVO LE?N' => 'NUEVO LEÓN',
        'SAN LUIS POTOS?' => 'SAN LUIS POTOSÍ',
        'CIUDAD DE M?XICO' => 'CIUDAD DE MÉXICO',
        'LE?N' => 'LEÓN',
        'C?RDOBA' => 'CÓRDOBA',
        'TORRE?N' => 'TORREÓN',
        'G?MEZ PALACIO' => 'GÓMEZ PALACIO',
        'CHIHUAHUA.' => 'CHIHUAHUA',
        'COAHUILA DE ZARAGOZA.' => 'COAHUILA DE ZARAGOZA',
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') ?? 100000;
        $force = $this->option('force');
        
        $this->info($dryRun ? '=== MODO SIMULACIÓN (--dry-run) ===' : '=== CORRECCIÓN DE ENCODING ===');
        $this->newLine();

        try {
            DB::beginTransaction();
            
            // 1. Contar registros afectados
            $this->info('Analizando base de datos...');
            $total = DB::table('codigos_postales')
                ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
                ->count();
            
            $this->line("Registros a procesar: " . number_format($total));
            $this->newLine();
            
            if ($total == 0) {
                $this->success('¡No hay registros con problemas de encoding!');
                DB::rollBack();
                return 0;
            }
            
            if (!$force && !$this->confirm('¿Desea continuar con la corrección?', true)) {
                $this->warn('Operación cancelada');
                DB::rollBack();
                return 0;
            }
            
            $corregidos = 0;
            $bar = $this->output->createProgressBar(min($total, $limit));
            
            // 2. Procesar en lotes
            DB::table('codigos_postales')
                ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
                ->limit($limit)
                ->orderBy('id')
                ->chunk(100, function ($registros) use (&$corregidos, $bar, $dryRun) {
                    foreach ($registros as $registro) {
                        $updates = [];
                        
                        // Corregir estado
                        if (strpos($registro->estado, '?') !== false) {
                            $estadoCorregido = $this->corregirTexto($registro->estado);
                            if ($estadoCorregido !== $registro->estado) {
                                $updates['estado'] = $estadoCorregido;
                            }
                        }
                        
                        // Corregir municipio
                        if (strpos($registro->municipio ?? '', '?') !== false) {
                            $municipioCorregido = $this->corregirTexto($registro->municipio);
                            if ($municipioCorregido !== $registro->municipio) {
                                $updates['municipio'] = $municipioCorregido;
                            }
                        }
                        
                        // Corregir colonia
                        if (strpos($registro->colonia ?? '', '?') !== false) {
                            $coloniaCorregida = $this->corregirTexto($registro->colonia);
                            if ($coloniaCorregida !== $registro->colonia) {
                                $updates['colonia'] = $coloniaCorregida;
                            }
                        }
                        
                        // Aplicar cambios
                        if (!empty($updates) && !$dryRun) {
                            DB::table('codigos_postales')
                                ->where('id', $registro->id)
                                ->update($updates);
                            $corregidos++;
                        } elseif (!empty($updates)) {
                            $corregidos++;
                        }
                        
                        $bar->advance();
                    }
                });
            
            $bar->finish();
            $this->newLine(2);
            
            if ($dryRun) {
                $this->info("SIMULACIÓN: Se corregirían $corregidos registros");
                $this->line("Ejecute sin --dry-run para aplicar los cambios");
                DB::rollBack();
            } else {
                DB::commit();
                $this->success("✓ Se corrigieron $corregidos registros exitosamente");
            }
            
            // Mostrar ejemplos después de la corrección
            if (!$dryRun) {
                $this->newLine();
                $this->info('Verificación de estados corregidos:');
                $estados = DB::table('codigos_postales')
                    ->select('estado')
                    ->distinct()
                    ->orderBy('estado')
                    ->limit(10)
                    ->pluck('estado');
                
                foreach ($estados as $estado) {
                    $this->line('  - ' . $estado);
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }

    protected function corregirTexto($texto)
    {
        if (empty($texto)) {
            return $texto;
        }
        
        // Primero intentar reemplazos completos
        foreach ($this->reemplazos as $mal => $bien) {
            if (stripos($mal, '?') !== false && stripos($texto, $mal) !== false) {
                $texto = str_ireplace($mal, $bien, $texto);
            }
        }
        
        // Luego reemplazos por contexto común
        // México, Michoacán, etc.
        $texto = preg_replace('/M\?XICO/i', 'MÉXICO', $texto);
        $texto = preg_replace('/MICHOAC\?N/i', 'MICHOACÁN', $texto);
        $texto = preg_replace('/QUER\?TARO/i', 'QUERÉTARO', $texto);
        $texto = preg_replace('/YUCAT\?N/i', 'YUCATÁN', $texto);
        $texto = preg_replace('/LE\?N/i', 'LEÓN', $texto);
        $texto = preg_replace('/C\?RDOBA/i', 'CÓRDOBA', $texto);
        $texto = preg_replace('/TORRE\?N/i', 'TORREÓN', $texto);
        $texto = preg_replace('/G\?MEZ/i', 'GÓMEZ', $texto);
        $texto = preg_replace('/POTOS\?/i', 'POTOSÍ', $texto);
        $texto = preg_replace('/MART\?NEZ/i', 'MARTÍNEZ', $texto);
        $texto = preg_replace('/RAM\?REZ/i', 'RAMÍREZ', $texto);
        $texto = preg_replace('/G\?LVEZ/i', 'GÁLVEZ', $texto);
        $texto = preg_replace('/JU\?REZ/i', 'JUÁREZ', $texto);
        $texto = preg_replace('/L\?PEZ/i', 'LÓPEZ', $texto);
        $texto = preg_replace('/P\?REZ/i', 'PÉREZ', $texto);
        $texto = preg_replace('/HERN\?NDEZ/i', 'HERNÁNDEZ', $texto);
        $texto = preg_replace('/S\?NCHEZ/i', 'SÁNCHEZ', $texto);
        $texto = preg_replace('/DOM\?NGUEZ/i', 'DOMÍNGUEZ', $texto);
        $texto = preg_replace('/GUTI\?RREZ/i', 'GUTIÉRREZ', $texto);
        $texto = preg_replace('/RODR\?GUEZ/i', 'RODRÍGUEZ', $texto);
        $texto = preg_replace('/GON(\?|Z)LEZ/i', 'GONZÁLEZ', $texto);
        
        // Reemplazos genéricos (última opción)
        // ? al final de palabra probablemente es Í
        $texto = preg_replace('/\?(?=\s|$)/u', 'Í', $texto);
        // ? en medio de palabra probablemente es É
        $texto = preg_replace('/\?(?=[A-Z])/u', 'É', $texto);
        
        return $texto;
    }
}
