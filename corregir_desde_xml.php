<?php
/**
 * Corrige codigos_postales usando CPdescarga.xml (SEPOMEX oficial)
 *
 * Estrategia eficiente:
 *  1. Carga SOLO los registros con '?' de la BD (mas pequeño) indexados por CP
 *  2. Streams el XML registro a registro con XMLReader
 *  3. Para cada registro XML, busca el match en el dict de BD usando Levenshtein
 *  4. Ejecuta UPDATE masivo (VALUES) cada 500 coincidencias
 *
 * Uso:
 *   php corregir_desde_xml.php            <- aplica cambios
 *   php corregir_desde_xml.php --dry-run  <- solo muestra estadisticas
 */

ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$dryRun  = in_array('--dry-run', $argv ?? []);
$xmlPath = __DIR__ . '/public/CPdescarga.xml';

echo ($dryRun ? "=== MODO SIMULACION ===" : "=== CORRECCION DESDE XML OFICIAL ===") . "\n\n";
flush();

// -------------------------------------------------------
// FASE 1 - Cargar registros corruptos de la BD
// -------------------------------------------------------
echo "Cargando registros con '?' de la BD...\n";
flush();

$dbByCP = []; // [codigo_postal => [id => [colonia_norm, qmarks]]]

DB::table('codigos_postales')
    ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
    ->select('id', 'codigo_postal', 'colonia')
    ->orderBy('id')
    ->chunk(5000, function ($rows) use (&$dbByCP) {
        foreach ($rows as $row) {
            $dbByCP[$row->codigo_postal][$row->id] = [
                'colonia_norm' => normalizar($row->colonia),
                'qmarks'       => substr_count($row->colonia, '?'),
            ];
        }
    });

$totalDB = array_sum(array_map('count', $dbByCP));
echo "Registros con '?': " . number_format($totalDB) . "\n";
echo "CPs afectados:     " . number_format(count($dbByCP)) . "\n\n";
flush();

if ($totalDB === 0) {
    echo "Nada que corregir.\n";
    exit(0);
}

// -------------------------------------------------------
// FASE 2 - Stream XML y matching
// -------------------------------------------------------
echo "Procesando XML (stream)...\n";
flush();

libxml_use_internal_errors(true);

$reader = new XMLReader();
if (!$reader->open($xmlPath, null, LIBXML_NOERROR | LIBXML_NOWARNING)) {
    echo "ERROR abriendo XML: $xmlPath\n";
    exit(1);
}

$pendingUpdates = [];
$matched        = 0;
$applied        = 0;
$batchSize      = 500;
$xmlCount       = 0;

while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'table') {
        continue;
    }

    $xmlCount++;
    $xml     = new SimpleXMLElement($reader->readOuterXml(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $dCodigo = trim((string) getField($xml, 'd_codigo'));

    if ($dCodigo === '' || !isset($dbByCP[$dCodigo])) {
        continue;
    }

    $xmlColoniaU = mb_strtoupper(trim((string) getField($xml, 'd_asenta')), 'UTF-8');
    $xmlNorm     = normalizar($xmlColoniaU);

    $nuevoEstado = mb_strtoupper(trim((string) getField($xml, 'd_estado')),      'UTF-8');
    $nuevoMnpio  = mb_strtoupper(trim((string) getField($xml, 'D_mnpio')),       'UTF-8');
    $nuevaCiudad = mb_strtoupper(trim((string) getField($xml, 'd_ciudad')),      'UTF-8');
    $nuevoTipo   = mb_strtoupper(trim((string) getField($xml, 'd_tipo_asenta')), 'UTF-8');
    $nuevaZona   = mb_strtoupper(trim((string) getField($xml, 'd_zona')),        'UTF-8');

    foreach ($dbByCP[$dCodigo] as $id => $dbRow) {
        if (isset($pendingUpdates[$id])) continue;

        // Comparar normalizados
        $isMatch = ($dbRow['colonia_norm'] === $xmlNorm);

        if (!$isMatch && $dbRow['qmarks'] > 0) {
            $lenDiff = strlen($xmlNorm) - strlen($dbRow['colonia_norm']);
            if ($lenDiff >= 0 && $lenDiff <= $dbRow['qmarks']) {
                $isMatch = (levenshtein($dbRow['colonia_norm'], $xmlNorm) <= $dbRow['qmarks']);
            }
        }

        if ($isMatch) {
            $pendingUpdates[$id] = [$id, $nuevoEstado, $nuevoMnpio, $nuevaCiudad, $xmlColoniaU, $nuevoTipo, $nuevaZona];
            $matched++;

            if (!$dryRun && count($pendingUpdates) >= $batchSize) {
                bulkUpdate($pendingUpdates);
                $applied += count($pendingUpdates);
                $pendingUpdates = [];
                $pct = round(($matched / $totalDB) * 100, 1);
                echo "  Actualizados: " . number_format($applied) . " / " . number_format($totalDB) . " ({$pct}%)\n";
                flush();
            }
        }
    }
}
$reader->close();

// Ultimo lote
if (!$dryRun && count($pendingUpdates) > 0) {
    bulkUpdate($pendingUpdates);
    $applied += count($pendingUpdates);
}

echo "\nXML procesado: " . number_format($xmlCount) . " registros\n";
echo "Match encontrado: " . number_format($matched) . " / " . number_format($totalDB) . "\n";

if ($dryRun) {
    echo "Sin match: " . number_format($totalDB - $matched) . "\n";
    echo "\nEjecuta sin --dry-run para aplicar los cambios.\n";
} else {
    echo "Actualizados en BD: " . number_format($applied) . "\n";
    echo "Sin match: " . number_format($totalDB - $matched) . "\n";
}

// -------------------------------------------------------
// Estado final
// -------------------------------------------------------
echo "\n=== ESTADO FINAL ===\n";
$r = DB::selectOne(
    "SELECT
        COUNT(*) FILTER (WHERE estado    LIKE '%?%') AS e,
        COUNT(*) FILTER (WHERE municipio LIKE '%?%') AS m,
        COUNT(*) FILTER (WHERE colonia   LIKE '%?%') AS c
     FROM codigos_postales"
);
echo "  estados    con '?': {$r->e}\n";
echo "  municipios con '?': {$r->m}\n";
echo "  colonias   con '?': {$r->c}\n";
if ($r->e == 0 && $r->m == 0 && $r->c == 0) {
    echo "\nTodos los acentos corregidos exitosamente!\n";
}

// -------------------------------------------------------
// Helpers
// -------------------------------------------------------
function getField(SimpleXMLElement $node, string $name): ?SimpleXMLElement
{
    foreach ($node->children() as $child) {
        if (strtolower($child->getName()) === strtolower($name)) return $child;
    }
    return null;
}

function normalizar(string $s): string
{
    $s    = mb_strtoupper($s, 'UTF-8');
    $from = ['A','E','I','O','U','U','N','A','E','I','O','U','A','E','I','O','U','A','E','I','O','A','O',
             "\xC3\x81","\xC3\x89","\xC3\x8D","\xC3\x93","\xC3\x9A","\xC3\x9C","\xC3\x91",
             "\xC3\x80","\xC3\x88","\xC3\x8C","\xC3\x92","\xC3\x99"];
    $to   = ['A','E','I','O','U','U','N','A','E','I','O','U','A','E','I','O','U','A','E','I','O','A','O',
             'A','E','I','O','U','U','N','A','E','I','O','U'];
    $from = ['Á','É','Í','Ó','Ú','Ü','Ñ','À','È','Ì','Ò','Ù','Â','Ê','Î','Ô','Û','Ä','Ë','Ï','Ö','Ã','Õ','á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù'];
    $to   = ['A','E','I','O','U','U','N','A','E','I','O','U','A','E','I','O','U','A','E','I','O','A','O','A','E','I','O','U','U','N','A','E','I','O','U'];
    $s    = str_replace($from, $to, $s);
    $s    = preg_replace('/[^A-Z0-9 ]/u', '', $s);
    return preg_replace('/\s+/', ' ', trim($s));
}

function bulkUpdate(array $updates): void
{
    if (empty($updates)) return;

    $rows = [];
    $pdo  = DB::getPdo();
    foreach ($updates as $upd) {
        [$id, $estado, $municipio, $ciudad, $colonia, $tipo, $zona] = $upd;
        $e  = $pdo->quote($estado   === null ? '' : $estado);
        $m  = $pdo->quote($municipio === null ? '' : $municipio);
        $ci = $pdo->quote($ciudad   === null ? '' : $ciudad);
        $co = $pdo->quote($colonia  === null ? '' : $colonia);
        $t  = $pdo->quote($tipo     === null ? '' : $tipo);
        $z  = $pdo->quote($zona     === null ? '' : $zona);
        $rows[] = "({$id}::bigint,{$e},{$m},{$ci},{$co},{$t},{$z})";
    }

    DB::statement(
        "UPDATE codigos_postales AS c
         SET estado=v.estado, municipio=v.municipio, ciudad=v.ciudad,
             colonia=v.colonia, tipo_asentamiento=v.tipo, zona=v.zona
         FROM (VALUES " . implode(',', $rows) . ")
              AS v(id,estado,municipio,ciudad,colonia,tipo,zona)
         WHERE c.id = v.id"
    );
}
