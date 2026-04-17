<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

// Conteo rápido del estado actual
$r = DB::selectOne("SELECT
    COUNT(*) FILTER (WHERE estado LIKE '%?%') as estados,
    COUNT(*) FILTER (WHERE municipio LIKE '%?%') as municipios,
    COUNT(*) FILTER (WHERE colonia LIKE '%?%') as colonias,
    COUNT(*) FILTER (WHERE estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%') as total
FROM codigos_postales");

echo "ESTADO ACTUAL:\n";
echo "  estados  con ?: {$r->estados}\n";
echo "  municipios con ?: {$r->municipios}\n";
echo "  colonias   con ?: {$r->colonias}\n";
echo "  TOTAL: {$r->total}\n";

// Mostrar ejemplo de municipio corregido
echo "\nEjemplos de municipios (con y sin ?):\n";
$ok   = DB::table('codigos_postales')->select('municipio')->whereRaw("municipio NOT LIKE '%?%'")->distinct()->orderBy('municipio')->limit(5)->pluck('municipio');
$fail = DB::table('codigos_postales')->select('municipio')->whereRaw("municipio LIKE '%?%'")->distinct()->orderBy('municipio')->limit(5)->pluck('municipio');

echo "OK: " . implode(', ', $ok->toArray()) . "\n";
echo "CON ?: " . implode(', ', $fail->toArray()) . "\n";
