<?php
// Obtener TODOS los municipios y colonias distintos con ? para crear el mapa completo
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// ESTADOS restantes
$estados = DB::table('codigos_postales')->select('estado')
    ->whereRaw("estado LIKE '%?%'")->distinct()->orderBy('estado')->pluck('estado');

echo "=== ESTADOS CON ? (" . $estados->count() . ") ===\n";
foreach ($estados as $e) { echo "  $e\n"; }

// MUNICIPIOS con ?
$municipios = DB::table('codigos_postales')->select('municipio')
    ->whereRaw("municipio LIKE '%?%'")->distinct()->orderBy('municipio')->pluck('municipio');

echo "\n=== MUNICIPIOS CON ? (" . $municipios->count() . ") ===\n";
foreach ($municipios as $m) { echo "  $m\n"; }

// COLONIAS con ? (obtenemos más para el análisis)
$colonias = DB::table('codigos_postales')->select('colonia')
    ->whereRaw("colonia LIKE '%?%'")->distinct()->orderBy('colonia')->pluck('colonia');

echo "\n=== COLONIAS CON ? (" . $colonias->count() . ", primeras 200) ===\n";
foreach ($colonias->take(200) as $c) { echo "  $c\n"; }

// Análisis de patrones - qué secuencias aparecen alrededor del ?
echo "\n=== ANÁLISIS DE PATRONES (contexto alrededor del ?) ===\n";
$patrones = [];
foreach (array_merge($municipios->toArray(), $colonias->take(500)->toArray()) as $texto) {
    // Encontrar posiciones del ?
    $pos = 0;
    while (($pos = strpos($texto, '?', $pos)) !== false) {
        $antes = $pos > 0 ? substr($texto, max(0, $pos-2), 2) : '';
        $despues = $pos < strlen($texto)-1 ? substr($texto, $pos+1, 2) : '';
        $contexto = $antes . '?' . $despues;
        $patrones[$contexto] = ($patrones[$contexto] ?? 0) + 1;
        $pos++;
    }
}
arsort($patrones);
echo "Contextos más frecuentes:\n";
foreach (array_slice($patrones, 0, 30, true) as $patron => $count) {
    echo "  '$patron': $count veces\n";
}
