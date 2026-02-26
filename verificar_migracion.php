<?php
/**
 * Script de Verificación Post-Migración
 * Verifica que el sistema esté funcionando correctamente después de la migración
 */

echo "🔍 VERIFICACIÓN POST-MIGRACIÓN - ZarzaPoints\n";
echo "==============================================\n\n";

$errores = [];
$advertencias = [];
$exitoso = [];

// 1. Verificar Conexión a Base de Datos
echo "1️⃣ VERIFICANDO CONEXIÓN A BASE DE DATOS...\n";
try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=postgres";
    $pdo = new PDO($dsn, 'appwebuser', 'appwebpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET search_path TO appweb, public");
    echo "   ✅ Conexión exitosa\n\n";
    $exitoso[] = "Conexión a base de datos";
} catch (PDOException $e) {
    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n\n";
    $errores[] = "Conexión a base de datos: " . $e->getMessage();
    die("⛔ No se puede continuar sin conexión a BD\n");
}

// 2. Verificar Tablas Esenciales
echo "2️⃣ VERIFICANDO TABLAS ESENCIALES...\n";
$tablasRequeridas = [
    'usuarios', 'direcciones', 'puntos', 'sucursales', 'compras',
    'transacciones_puntos', 'cupones', 'cupones_asignados', 'redenciones', 'auditoria'
];

foreach ($tablasRequeridas as $tabla) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
        $count = $stmt->fetchColumn();
        echo "   ✅ $tabla: $count registros\n";
        $exitoso[] = "Tabla $tabla existe";
    } catch (PDOException $e) {
        echo "   ❌ $tabla: NO EXISTE\n";
        $errores[] = "Tabla $tabla no existe";
    }
}
echo "\n";

// 3. Verificar Estructura de cupones
echo "3️⃣ VERIFICANDO ESTRUCTURA DE CUPONES...\n";
try {
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_schema = 'appweb' 
        AND table_name = 'cupones' 
        ORDER BY ordinal_position
    ");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnasRequeridas = ['id', 'nombre', 'codigo', 'descripcion', 'puntos_requeridos', 'activo'];
    foreach ($columnasRequeridas as $col) {
        if (in_array($col, $columnas)) {
            echo "   ✅ Columna '$col' existe\n";
            $exitoso[] = "Columna cupones.$col";
        } else {
            echo "   ❌ Columna '$col' NO EXISTE\n";
            $errores[] = "Columna cupones.$col no existe";
        }
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $errores[] = "Error verificando estructura de cupones";
}
echo "\n";

// 4. Verificar Estructura de cupones_asignados
echo "4️⃣ VERIFICANDO ESTRUCTURA DE CUPONES_ASIGNADOS...\n";
try {
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_schema = 'appweb' 
        AND table_name = 'cupones_asignados' 
        ORDER BY ordinal_position
    ");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnasRequeridas = ['id', 'usuario_id', 'cupon_id', 'estado', 'codigo_qr', 'fecha_uso', 'validado_por'];
    foreach ($columnasRequeridas as $col) {
        if (in_array($col, $columnas)) {
            echo "   ✅ Columna '$col' existe\n";
            $exitoso[] = "Columna cupones_asignados.$col";
        } else {
            echo "   ❌ Columna '$col' NO EXISTE\n";
            $errores[] = "Columna cupones_asignados.$col no existe";
        }
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $errores[] = "Error verificando estructura de cupones_asignados";
}
echo "\n";

// 5. Verificar Usuarios de Prueba
echo "5️⃣ VERIFICANDO USUARIOS DE PRUEBA...\n";
try {
    $stmt = $pdo->query("SELECT email, rol FROM usuarios WHERE email IN ('cliente@test.com', 'admin@test.com')");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) >= 2) {
        foreach ($usuarios as $user) {
            echo "   ✅ {$user['email']} ({$user['rol']})\n";
            $exitoso[] = "Usuario {$user['email']}";
        }
    } else {
        echo "   ⚠️ Faltan usuarios de prueba\n";
        $advertencias[] = "Faltan usuarios de prueba (cliente@test.com, admin@test.com)";
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $errores[] = "Error verificando usuarios";
}
echo "\n";

// 6. Verificar Cupones con Código
echo "6️⃣ VERIFICANDO CUPONES CON CÓDIGO...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM cupones WHERE codigo IS NOT NULL AND codigo != ''");
    $countConCodigo = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM cupones");
    $countTotal = $stmt->fetchColumn();
    
    echo "   📊 Total cupones: $countTotal\n";
    echo "   📊 Con código: $countConCodigo\n";
    
    if ($countConCodigo > 0) {
        echo "   ✅ Cupones tienen códigos asignados\n";
        $exitoso[] = "Cupones con códigos";
    } else {
        echo "   ⚠️ Ningún cupón tiene código asignado\n";
        $advertencias[] = "Cupones sin códigos - ejecutar update_cupones_structure.php";
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $errores[] = "Error verificando códigos de cupones";
}
echo "\n";

// 7. Verificar Archivos Críticos
echo "7️⃣ VERIFICANDO ARCHIVOS CRÍTICOS...\n";
$archivosRequeridos = [
    '.env' => 'Configuración del sistema',
    'artisan' => 'CLI de Laravel',
    'composer.json' => 'Dependencias PHP',
    'app/Http/Controllers/Web/CouponsController.php' => 'Controlador de Cupones',
    'app/Models/Cupon.php' => 'Modelo Cupon',
    'app/Models/CuponAsignado.php' => 'Modelo CuponAsignado',
    'resources/views/client/coupons/index.blade.php' => 'Vista de Cupones Cliente',
];

foreach ($archivosRequeridos as $archivo => $descripcion) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "   ✅ $archivo\n";
        $exitoso[] = "Archivo $archivo";
    } else {
        echo "   ❌ $archivo - NO EXISTE\n";
        $errores[] = "Archivo $archivo no existe";
    }
}
echo "\n";

// 8. Verificar Permisos de Escritura
echo "8️⃣ VERIFICANDO PERMISOS DE ESCRITURA...\n";
$carpetasEscritura = ['storage/logs', 'storage/app', 'bootstrap/cache'];

foreach ($carpetasEscritura as $carpeta) {
    $path = __DIR__ . '/' . $carpeta;
    if (is_dir($path) && is_writable($path)) {
        echo "   ✅ $carpeta - escribible\n";
        $exitoso[] = "Permisos en $carpeta";
    } else {
        echo "   ⚠️ $carpeta - NO escribible o no existe\n";
        $advertencias[] = "Carpeta $carpeta sin permisos de escritura";
    }
}
echo "\n";

// 9. Verificar Extensiones PHP
echo "9️⃣ VERIFICANDO EXTENSIONES PHP...\n";
$extensionesRequeridas = ['pdo_pgsql', 'pgsql', 'mbstring', 'openssl', 'json', 'gd'];

foreach ($extensionesRequeridas as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext\n";
        $exitoso[] = "Extensión PHP $ext";
    } else {
        echo "   ❌ $ext - NO INSTALADA\n";
        $errores[] = "Extensión PHP $ext no está instalada";
    }
}
echo "\n";

// 10. Verificar Versiones
echo "🔟 VERIFICANDO VERSIONES...\n";
echo "   📌 PHP: " . phpversion() . "\n";

if (version_compare(phpversion(), '8.1.0', '>=')) {
    echo "   ✅ Versión PHP compatible\n";
    $exitoso[] = "Versión PHP";
} else {
    echo "   ❌ PHP debe ser 8.1 o superior\n";
    $errores[] = "Versión PHP incompatible";
}

try {
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "   📌 PostgreSQL: $version\n";
    $exitoso[] = "PostgreSQL instalado";
} catch (PDOException $e) {
    echo "   ❌ No se pudo obtener versión de PostgreSQL\n";
}
echo "\n";

// RESUMEN FINAL
echo "═══════════════════════════════════════════════════\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "✅ EXITOSOS: " . count($exitoso) . "\n";
if (!empty($exitoso)) {
    foreach (array_slice($exitoso, 0, 5) as $item) {
        echo "   • $item\n";
    }
    if (count($exitoso) > 5) {
        echo "   • ... y " . (count($exitoso) - 5) . " más\n";
    }
}
echo "\n";

if (!empty($advertencias)) {
    echo "⚠️ ADVERTENCIAS: " . count($advertencias) . "\n";
    foreach ($advertencias as $adv) {
        echo "   • $adv\n";
    }
    echo "\n";
}

if (!empty($errores)) {
    echo "❌ ERRORES: " . count($errores) . "\n";
    foreach ($errores as $err) {
        echo "   • $err\n";
    }
    echo "\n";
    echo "⛔ RESULTADO: MIGRACIÓN INCOMPLETA\n";
    echo "   Revisa los errores antes de continuar\n\n";
} else {
    echo "🎉 RESULTADO: ¡MIGRACIÓN EXITOSA!\n";
    echo "   El sistema está listo para usarse\n\n";
    
    echo "🌐 URLs de Acceso:\n";
    echo "   • Frontend: http://localhost:8000\n";
    echo "   • Login: http://localhost:8000/login\n";
    echo "   • Admin: http://localhost:8000/admin/points\n\n";
    
    echo "🔑 Credenciales de Prueba:\n";
    echo "   • Cliente: cliente@test.com / password\n";
    echo "   • Admin: admin@test.com / password\n\n";
}

echo "═══════════════════════════════════════════════════\n";
