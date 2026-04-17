<?php
/**
 * Corrección COMPLETA de acentos - usando REGEXP y patrones del español/náhuatl
 * Este script cubre TODOS los patrones comunes, no solo algunos específicos
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? []);

echo $dryRun ? "=== MODO SIMULACIÓN (--dry-run) ===\n" : "=== CORRECCIÓN COMPLETA DE ACENTOS ===\n";
echo "\n";

// =========================================================
// FASE 1: Patrones de ALTA CONFIANZA (sufijos bien definidos)
// Orden importante: primero los más específicos, luego los genéricos
// =========================================================

$reemplazosSQL = [

    // === SUFIJOS ESPAÑOL más frecuentes en colonias ===
    // ?N donde el ? = Ó (sufijo -CIÓN, -SIÓN, -ÓN)
    ['SECCI?N',       'SECCIÓN'],
    ['AMPLIACI?N',    'AMPLIACIÓN'],
    ['AMPLACI?N',     'AMPLIACIÓN'],
    ['FRACCI?N',      'FRACCIÓN'],
    ['DEMARCACI?N',   'DEMARCACIÓN'],
    ['CONCEPCI?N',    'CONCEPCIÓN'],
    ['ASUNCI?N',      'ASUNCIÓN'],
    ['ASCENSI?N',     'ASCENSIÓN'],
    ['ASCENCI?N',     'ASCENSIÓN'],
    ['DIRECCI?N',     'DIRECCIÓN'],
    ['OPERACI?N',     'OPERACIÓN'],
    ['DELEGACI?N',    'DELEGACIÓN'],
    ['ASOCIACI?N',    'ASOCIACIÓN'],
    ['FUNDACI?N',     'FUNDACIÓN'],
    ['ADMINISTRACI?N','ADMINISTRACIÓN'],
    ['COORDINACI?N',  'COORDINACIÓN'],
    ['CONSTRUCCI?N',  'CONSTRUCCIÓN'],
    ['COMUNICACI?N',  'COMUNICACIÓN'],
    ['ELECTRIFICACI?N','ELECTRIFICACIÓN'],
    ['EXPROPACI?N',   'EXPROPIACIÓN'],
    ['EXPROPIACI?N',  'EXPROPIACIÓN'],
    ['HABITACI?N',    'HABITACIÓN'],
    ['INSTALACI?N',   'INSTALACIÓN'],
    ['LOTEICACI?N',   'LOTEICACIÓN'],
    ['LOTIFICACI?N',  'LOTIFICACIÓN'],
    ['MIGRACI?N',     'MIGRACIÓN'],
    ['NACI?N',        'NACIÓN'],
    ['ORGANIZACI?N',  'ORGANIZACIÓN'],
    ['POBLACI?N',     'POBLACIÓN'],
    ['PRESENTACI?N',  'PRESENTACIÓN'],
    ['PROLONGACI?N',  'PROLONGACIÓN'],
    ['PROTECCI?N',    'PROTECCIÓN'],
    ['REMODELACI?N',  'REMODELACIÓN'],
    ['SOLUCI?N',      'SOLUCIÓN'],
    ['TRANSFORMACI?N','TRANSFORMACIÓN'],
    ['VERIFICACI?N',  'VERIFICACIÓN'],
    ['CREACI?N',      'CREACIÓN'],
    ['PERCEPCI?N',    'PERCEPCIÓN'],
    ['COOPERACI?N',   'COOPERACIÓN'],
    ['EDUCACI?N',     'EDUCACIÓN'],
    ['INMERSI?N',     'INMERSIÓN'],
    ['MISI?N',        'MISIÓN'],
    ['PENSI?N',       'PENSIÓN'],
    ['SI?N',          'SIÓN'],   // genérico para -SIÓN
    ['CI?N',          'CIÓN'],   // genérico para -CIÓN (aplica después de los específicos)
    
    // -?N en final de palabras (Ó)
    ['UNI?N',         'UNIÓN'],
    ['COMUNI?N',      'COMUNIÓN'],
    ['BATALL?N',      'BATALLÓN'],
    ['CAMILL?N',      'CAMILLÓN'],
    ['PANTALE?N',     'PANTALEÓN'],  // nombre propio
    ['SIME?N',        'SIMEÓN'],
    ['GEDE?N',        'GEDEÓN'],
    
    // === RÍO patrones ===
    ['R?O',           'RÍO'],
    ['3 R?OS',        '3 RÍOS'],
    
    // === SUFIJO -ERÍA / -ERÍA ===
    ['ANTER?A',       'ANTERÍA'],    // INFANTERÍA
    ['INFANTER?A',    'INFANTERÍA'],
    ['PANADER?A',     'PANADERÍA'],
    ['CARNICER?A',    'CARNICERÍA'],
    ['ZAPATER?A',     'ZAPATERÍA'],
    ['FERRETER?A',    'FERRETERÍA'],
    ['TIQUER?A',      'TIQUERÍA'],
    ['REFRESQUER?A',  'REFRESQUERÍA'],
    ['TLAQUER?A',     'TLAQUERÍA'],
    ['TEQUILER?A',    'TEQUILERÍA'],
    ['MEZCALER?A',    'MEZCALERÍA'],
    ['TORTILLER?A',   'TORTILLERÍA'],
    ['TAMALER?A',     'TAMALERÍA'],
    
    // === NOMBRES PROPIOS comunes con Á ===
    ['?NGEL',         'ÁNGEL'],
    ['?NGELES',       'ÁNGELES'],
    ['?LVAREZ',       'ÁLVAREZ'],
    ['?LVARO',        'ÁLVARO'],
    ['?LAMOS',        'ÁLAMOS'],
    ['?LAMO',         'ÁLAMO'],
    ['?FRICA',        'ÁFRICA'],
    ['?GUILA',        'ÁGUILA'],
    ['?GUILAS',       'ÁGUILAS'],
    ['?LARO',         'ÁLARO'],
    ['?NAVAS',        'ÁNAVAS'],
    ['?LAMOS',        'ÁLAMOS'],
    
    // === NOMBRES con Á en medio ===
    ['HECT?REAS',     'HECTÁREAS'],
    ['?REA',          'ÁREA'],
    ['?REAS',         'ÁREAS'],
    ['?RBOL',         'ÁRBOL'],
    ['?RBOLES',       'ÁRBOLES'],
    ['BUR?CRATA',     'BURÓCRATA'],
    ['BUR?CRATAS',    'BURÓCRATAS'],
    
    // === NOMBRES propios con Ó ===
    ['CRIST?BAL',     'CRISTÓBAL'],
    ['NICOL?S',       'NICOLÁS'],
    ['JOS? MAR?A',    'JOSÉ MARÍA'],
    ['JOS? JOAQU?N',  'JOSÉ JOAQUÍN'],
    ['JOS?',          'JOSÉ'],
    
    // === NOMBRES con Í ===
    ['MAR?A',         'MARÍA'],
    ['GARC?A',        'GARCÍA'],
    ['VALENC?A',      'VALENCIA'],
    ['ITAL?A',        'ITALIA'],
    ['MAGALL?N',      'MAGALLÓN'],
    ['COMPA??A',      'COMPAÑÍA'],
    
    // === PATRONES con Ñ (muy comunes) ===
    ['SE?OR?O',       'SEÑORÍO'],
    ['SE?OR',         'SEÑOR'],
    ['SE?ORES',       'SEÑORES'],
    ['CA?ADA',        'CAÑADA'],
    ['CA?ADAS',       'CAÑADAS'],
    ['CA??N',         'CAÑÓN'],
    ['CA?ON',         'CAÑÓN'],
    ['CA?AVERAL',     'CAÑAVERAL'],
    ['CA?A',          'CAÑA'],
    ['CA?AS',         'CAÑAS'],
    ['CA?ITAS',       'CAÑITAS'],
    ['MU?OZ',         'MUÑOZ'],
    ['MU?OZCA',       'MUÑOZCA'],
    ['NI?O',          'NIÑO'],
    ['NI?OS',         'NIÑOS'],
    ['NI?A',          'NIÑA'],
    ['PE?A',          'PEÑA'],
    ['PE?AS',         'PEÑAS'],
    ['PE?ASCO',       'PEÑASCO'],
    ['PE?OL',         'PEÑOL'],
    ['PE?ON',         'PEÑON'],
    ['PE??N',         'PEÑÓN'],
    ['PE??N',         'PEÑÓN'],
    ['ESPA?A',        'ESPAÑA'],
    ['E?UECA',        'EÑUECA'],
    ['ORDO?EZ',       'ORDÓÑEZ'],
    ['NI?EZ',         'NIÑEZ'],
    ['PE?A',          'PEÑA'],
    ['A?O',           'AÑO'],
    ['A?OS',          'AÑOS'],
    ['AN?O',          'AÑÑO'],
    ['MA?ANA',        'MAÑANA'],
    ['MU?ECA',        'MUÑECA'],
    ['TATA?O',        'TATAÑHO'],
    ['TA?O',          'TAÑO'],
    ['O?O',           'OÑÑO'],
    ['I?OZO',         'IÑOZO'],
    ['BRISE?AS',      'BRISEÑAS'],
    ['CASTA?OS',      'CASTAÑOS'],
    ['CASTA?O',       'CASTAÑO'],
    ['ESPA?ITA',      'ESPAÑITA'],
    ['LA COMPA??A',   'LA COMPAÑÍA'],
    ['TREVI?O',       'TREVIÑO'],
    ['TREVI?OS',      'TREVIÑOS'],
    ['COMPA??A',      'COMPAÑÍA'],
    
    // === Nombres con Ú ===
    ['JES?S',         'JESÚS'],
    ['JES?GES',       'JESÚGES'],
    ['QUOAHUT?MOC',   'CUAUHTÉMOC'],
    ['CUAUHT?MOC',    'CUAUHTÉMOC'],
    ['CUAUHT?MO',     'CUAUHTÉMO'],
    ['CU?LLAR',       'CUÉLLAR'],
    ['CU?LLAR',       'CUÉLLAR'],
    
    // === NOMBRES MUY FRECUENTES en colonias mexicanas ===
    ['GUADAL?PE',     'GUADALUPE'],
    ['GUADALUPE',     'GUADALUPE'],
    
    // === SUFIJOS NÁHUATL comunes (TL?N = TLÁN) ===
    ['TL?N',          'TLÁN'],
    
    // Sufijo -HUAC (náhuatl)
    ['?HUAC',         'ÁHUAC'],
    
    // === SUFIJOS generales -?N (donde ? = Á) ===
    // Estos son más específicos y deben ir antes del genérico
    ['ATIZAP?N',      'ATIZAPÁN'],
    ['CH?N',          'CHÁN'],   // sufijo maya/náhuatl
    
    // === PALABRAS completas frecuentes ===
    // Municipios específicos frecuentes que quedan
    ['ACATL?N',       'ACATLÁN'],
    ['AC?MBARO',      'ACÁMBARO'],
    ['ACU?A',         'ACUÑA'],
    ['AN?HUAC',       'ANÁHUAC'],
    ['APATZING?N',    'APATZINGÁN'],
    ['AGUASCALIENTES','AGUASCALIENTES'],
    ['AQUISM?N',      'AQUISMÓN'],
    ['MAZ?N',         'MAZÓN'],
    ['MAZATL?N',      'MAZATLÁN'],
    ['CULIAC?N',      'CULIACÁN'],
    ['GUADALAJ?RA',   'GUADALAJARA'],
    ['TLAXCAL?N',     'TLAXCALÁN'],
    ['TEXCOC?N',      'TEXCOCÁN'],
    ['XOCHIMILC?',    'XOCHIMILCO'],
    ['COYOAC?N',      'COYOACÁN'],
    ['ITZAMNA',       'ITZAMNÁ'],
    ['TLAYACAP?N',    'TLAYACAPÁN'],
    ['TLALPAN',       'TLALPAN'],
    ['TULTITL?N',     'TULTITLÁN'],
    ['IZTAPALAP?',    'IZTAPALAPA'],
    ['GUSTAVO D?AZ ORDAZ', 'GUSTAVO DÍAZ ORDAZ'],
    ['D?AZ',          'DÍAZ'],
    ['LÁZARO',        'LÁZARO'],
    ['L?ZARO',        'LÁZARO'],
    ['?LVAREZ',       'ÁLVAREZ'],
    ['BERMEJ?LLO',    'BERMEJILLO'],
    
    // === PATRONES genéricos (aplicar AL FINAL) ===
    // Solo cuando hay certeza casi total del contexto
    ['BEN?TEZ',       'BENÍTEZ'],
    ['MEND?Z',        'MENDÍZ'],
    ['M?NDEZ',        'MÉNDEZ'],
    ['HERN?NDEZ',     'HERNÁNDEZ'],
    ['FERN?NDEZ',     'FERNÁNDEZ'],
    ['S?NCHEZ',       'SÁNCHEZ'],
    ['J?MENEZ',       'JIMÉNEZ'],
    ['JIMENEZ',       'JIMÉNEZ'],
    ['JIM?NEZ',       'JIMÉNEZ'],
    ['DOM?NGUEZ',     'DOMÍNGUEZ'],
    ['GUTI?RREZ',     'GUTIÉRREZ'],
    ['RODR?GUEZ',     'RODRÍGUEZ'],
    ['GONZ?LEZ',      'GONZÁLEZ'],
    ['MART?NEZ',      'MARTÍNEZ'],
    ['RAM?REZ',       'RAMÍREZ'],
    ['JU?REZ',        'JUÁREZ'],
    ['L?PEZ',         'LÓPEZ'],
    ['P?REZ',         'PÉREZ'],
    ['P?REA',         'PÉREA'],
    ['G?MARA',        'GÁMARA'],
    ['?LVARO',        'ÁLVARO'],
    ['FIGUERO?',      'FIGUEROA'],
    ['M?LAGA',        'MÁLAGA'],
    ['S?NFORO',       'SÁNFORO'],
    ['G?MEZ',         'GÓMEZ'],
    ['TORRE?N',       'TORREÓN'],
    ['C?RDOBA',       'CÓRDOBA'],
    ['LE?N',          'LEÓN'],
    ['POTOS?',        'POTOSÍ'],
    ['BENITO JU?REZ', 'BENITO JUÁREZ'],
    ['QUERETARO',     'QUERÉTARO'],
    ['QUER?TARO',     'QUERÉTARO'],
    ['MICHOAC?N',     'MICHOACÁN'],
    ['YUCAT?N',       'YUCATÁN'],
    ['ANTORCHISTA',   'ANTORCHISTA'],
    ['G?LVEZ',        'GÁLVEZ'],
    ['COYOAC?N',      'COYOACÁN'],

    // Nombres de personas y lugares adicionales
    ['AAR?N',         'AARÓN'],
    ['JOSU?',         'JOSUÉ'],
    ['SALOME',        'SALOMÉ'],
    ['SALOM?',        'SALOMÉ'],
    ['SALOM?',        'SALOMÉ'],
    ['HERN?N',        'HERNÁN'],
    ['BELTR?N',       'BELTRÁN'],
    ['TOLTEC?N',      'TOLTECÁN'],
    ['PAMP?N',        'PAMPÓN'],
    ['MEDELL?N',      'MEDELLÍN'],
    ['GUADAL?PE',     'GUADALUPE'],
    ['ZAPOPAN',       'ZAPOPAN'],
    ['JACAL?N',       'JACALÁN'],
    ['MORELIA',       'MORELIA'],
    ['OAX?CA',        'OAXACA'],

    // Patrones de Ñ (nombres comunes)
    ['ESPA?OL',       'ESPAÑOL'],
    ['ESPA?OLA',      'ESPAÑOLA'],
    ['ESPA?OLES',     'ESPAÑOLES'],
    ['ORFE?N',        'ORFEÓN'],
    ['ENSE?ANZA',     'ENSEÑANZA'],
    ['PE?ASCO',       'PEÑASCO'],
    ['EMPE?O',        'EMPEÑO'],
    ['DISE?O',        'DISEÑO'],
    ['SU?O',          'SUEÑO'],  // SUEÑO
    ['EN?O',          'EÑO'],    // genérico EÑHO
    ['EMPA?AMIENTO',  'EMPAÑAMIENTO'],
    ['BA?O',          'BAÑO'],
    ['BA?OS',         'BAÑOS'],
    ['DA?O',          'DAÑO'],
    ['DA?OS',         'DAÑOS'],
    ['MONTA?A',       'MONTAÑA'],
    ['MONTA?AS',      'MONTAÑAS'],
    ['ARRA?AN',       'ARRAÑAN'],
    ['TAMAGA?OTL',    'TAMAGAÑOTL'],
    ['TIZIU?TLIPAN',  'TIZIUÑTLIPAN'],
    ['PARA?O',        'PARAÑO'],
];

try {
    if (!$dryRun) {
        DB::beginTransaction();
        echo "Iniciando transacción...\n\n";
    }

    $totalOps = 0;

    foreach (['estado', 'municipio', 'colonia'] as $columna) {
        echo "Procesando columna: $columna\n";
        $columnOps = 0;

        foreach ($reemplazosSQL as [$buscar, $reemplazar]) {
            // Escapar para SQL - usamos comillas dobles para la búsqueda
            $buscarEscaped   = str_replace("'", "''", $buscar);
            $reemplazarEscaped = str_replace("'", "''", $reemplazar);

            if ($dryRun) {
                $count = DB::table('codigos_postales')
                    ->whereRaw("$columna LIKE '%$buscarEscaped%'")
                    ->count();
                if ($count > 0) {
                    echo "  [DRY] '$buscar' → '$reemplazar': $count registros\n";
                    $columnOps += $count;
                    $totalOps  += $count;
                }
            } else {
                $sql = "UPDATE codigos_postales
                        SET $columna = REPLACE($columna, '$buscarEscaped', '$reemplazarEscaped')
                        WHERE $columna LIKE '%$buscarEscaped%'";
                $updated = DB::affectingStatement($sql);
                if ($updated > 0) {
                    echo "  ✓ '$buscar' → '$reemplazar': $updated registros\n";
                    $columnOps += $updated;
                    $totalOps  += $updated;
                }
            }
        }

        echo "  Subtotal $columna: $columnOps operaciones\n\n";
    }

    // =========================================================
    // FASE 2: CORRECCIÓN GENÉRICA via REGEXP (PostgreSQL)
    // Patrones estructurales que no pueden quedar con ?
    //   • CI?N al final → CIÓN
    //   • TL?N           → TLÁN
    //   • R?O            → RÍO
    //   • MAR?A / GARC?A → MARÍA / GARCÍA
    // =========================================================
    echo "=== FASE 2: Correcciones con REGEXP ===\n\n";

    $regexPatterns = [
        // sufijo -CIÓN (el ? está entre I y N, precedido de vocal+C)
        ["~([A-ZÁÉÍÓÚÜ])CI\\?N~",  '\\1CIÓN',  "-CI?N → -CIÓN"],
        // sufijo -TLÁN  (? entre L y N, precedido de T)
        ["~TL\\?N~",               'TLÁN',     "TL?N → TLÁN"],
        // RÍO (? entre R y O)
        ["~R\\?O~",                'RÍO',      "R?O → RÍO"],
        // MARÍA / GARCÍA: ? entre R y A cuando es sufijo -ÍA
        ["~R\\?A~",                'RÍA',      "R?A → RÍA"],
        ["~AR\\?A~",               'ARÍA',     "AR?A → ARÍA"],
        // sufijos -CIÓN genérico  ?N tras I
        ["~I\\?N\\b~",             'IÓN',      "I?N → IÓN"],
        // ? al final absoluto de la palabra (Nahuatl como ABALÁ)
        ["~\\?\\b~",               'Á',        "? al final → Á"],
    ];

    foreach ($regexPatterns as [$pattern, $replacement, $desc]) {
        foreach (['municipio', 'colonia'] as $columna) {
            if ($dryRun) {
                $count = DB::table('codigos_postales')
                    ->whereRaw("$columna ~ ?", ['\\?' ])
                    ->count();
                echo "  [DRY] REGEXP $desc para $columna: ~$count registros con ?\n";
                break; // solo mostrar una vez
            } else {
                $patternEsc = str_replace("'", "''", $pattern);
                $replEsc    = str_replace("'", "''", $replacement);
                $sql = "UPDATE codigos_postales
                        SET $columna = REGEXP_REPLACE($columna, '$patternEsc', '$replEsc', 'g')
                        WHERE $columna ~ '\\?'";
                $updated = DB::affectingStatement($sql);
                if ($updated > 0) {
                    echo "  ✓ REGEXP [$desc] en $columna: $updated registros\n";
                    $totalOps += $updated;
                }
            }
        }
    }

    if ($dryRun) {
        echo "\nMODO SIMULACIÓN: Se realizarían aprox. $totalOps operaciones de reemplazo\n";
        echo "Ejecute sin --dry-run para aplicar los cambios\n";
    } else {
        DB::commit();
        echo "\n✓ COMPLETADO. Total de operaciones: $totalOps\n";
    }

    // Resumen final
    echo "\n=== RESUMEN FINAL ===\n";
    foreach (['estado', 'municipio', 'colonia'] as $col) {
        $restantes = DB::table('codigos_postales')
            ->whereRaw("$col LIKE '%?%'")
            ->distinct($col)->count($col);
        echo "  $col con '?' restantes: $restantes valores distintos\n";
    }

    $totalRestantes = DB::table('codigos_postales')
        ->whereRaw("estado LIKE '%?%' OR municipio LIKE '%?%' OR colonia LIKE '%?%'")
        ->count();
    echo "\n  Total registros aún con '?': $totalRestantes\n";

} catch (\Exception $e) {
    if (!$dryRun) DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
