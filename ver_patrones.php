<?php
// Script para ver los patrones con ? que quedan en municipios y colonias
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "MUNICIPIOS con '?' (primeros 80 distintos):\n";
$mun = DB::table('codigos_postales')
    ->select('municipio')
    ->whereRaw("municipio LIKE '%?%'")
    ->distinct()
    ->orderBy('municipio')
    ->limit(80)
    ->pluck('municipio');

foreach ($mun as $m) {
    echo "  - $m\n";
}

echo "\nTotal municipios con '?': " . DB::table('codigos_postales')->whereRaw("municipio LIKE '%?%'")->distinct('municipio')->count() . "\n";

echo "\n\nCOLONIAS con '?' (primeras 100 distintas):\n";
$col = DB::table('codigos_postales')
    ->select('colonia')
    ->whereRaw("colonia LIKE '%?%'")
    ->distinct()
    ->orderBy('colonia')
    ->limit(100)
    ->pluck('colonia');

foreach ($col as $c) {
    echo "  - $c\n";
}

echo "\nTotal colonias con '?': " . DB::table('codigos_postales')->whereRaw("colonia LIKE '%?%'")->distinct('colonia')->count() . "\n";

echo "\n\nESTADOS con '?' restantes:\n";
$est = DB::table('codigos_postales')
    ->select('estado')
    ->whereRaw("estado LIKE '%?%'")
    ->distinct()
    ->orderBy('estado')
    ->pluck('estado');

foreach ($est as $e) {
    echo "  - $e\n";
}
echo "Total: " . $est->count() . "\n";
