<?php
/**
 * Script de corrección rápida de encoding usando SQL directo
 * Este script es independiente y no interfiere con otros comandos
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv);

echo $dryRun ? "=== MODO SIMULACIÓN ===\n" : "=== CORRECCIÓN RÁPIDA DE ENCODING ===\n";
echo "\n";

// Reemplazos más comunes primero
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
    ['JU?REZ', 'JUÁREZ'],
    ['L?PEZ', 'LÓPEZ'],
    ['P?REZ', 'PÉREZ'],
    ['HERN?NDEZ', 'HERNÁNDEZ'],
    ['S?NCHEZ', 'SÁNCHEZ'],
    ['GUTI?RREZ', 'GUTIÉRREZ'],
    ['RODR?GUEZ', 'RODRÍGUEZ'],
    ['GONZ?LEZ', 'GONZÁLEZ'],
];

try {
    if (!$dryRun) {
        DB::beginTransaction();
        echo "Iniciando transacción...\n\n";
    }
    
    $totalActualizaciones = 0;
    
    foreach (['estado', 'municipio', 'colonia'] as $columna) {
        echo "Procesando columna: $columna\n";
        
        foreach ($reemplazos as $reemplazo) {
            [$buscar, $reemplazar] = $reemplazo;
            
            // Escapar comillas para SQL
            $buscarEscaped = str_replace("'", "''", $buscar);
            $reemplazarEscaped = str_replace("'", "''", $reemplazar);
            
            if ($dryRun) {
                $count = DB::table('codigos_postales')
                    ->where($columna, 'LIKE', '%' . $buscar . '%')
                    ->count();
                
                if ($count > 0) {
                    echo "  - Reemplazar '$buscar' → '$reemplazar': $count registros\n";
                    $totalActualizaciones += $count;
                }
            } else {
                $sql = "UPDATE codigos_postales 
                        SET $columna = REPLACE($columna, '$buscarEscaped', '$reemplazarEscaped')
                        WHERE $columna LIKE '%$buscarEscaped%'";
                
                $updated = DB::affectingStatement($sql);
                
                if ($updated > 0) {
                    echo "  - Reemplazado '$buscar' → '$reemplazar': $updated registros\n";
                    $totalActualizaciones += $updated;
                }
            }
        }
        echo "\n";
    }
    
    if ($dryRun) {
        echo "\n";
        echo "SIMULACIÓN: Se actualizarían aproximadamente $totalActualizaciones registros\n";
        echo "Ejecute sin --dry-run para aplicar los cambios\n";
    } else {
        DB::commit();
        echo "\n";
        echo "✓ Proceso completado. Total de operaciones UPDATE: $totalActualizaciones\n";
    }
    
    // Verificar cuántos quedan con problemas
    echo "\n";
    echo "Verificando registros restantes con problemas...\n";
    $restantes = DB::table('codigos_postales')
        ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
        ->count();
    
    echo "Registros que aún tienen '?': " . number_format($restantes) . "\n";
    
    if ($restantes > 0) {
        echo "\n";
        echo "Ejemplos de estados con '?':\n";
        $estados = DB::table('codigos_postales')
            ->select('estado')
            ->whereRaw("estado LIKE '%?%'")
            ->distinct()
            ->limit(5)
            ->pluck('estado');
        
        foreach ($estados as $estado) {
            echo "  - $estado\n";
        }
    } else {
        echo "\n";
        echo "¡Excelente! Todos los caracteres corruptos fueron corregidos.\n";
    }
    
    exit(0);
    
} catch (\Exception $e) {
    if (!$dryRun) {
        DB::rollBack();
        echo "\n";
        echo "ROLLBACK aplicado por error\n";
    }
    echo "\n";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
