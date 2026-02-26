<?php
/**
 * Script para verificar el estado de autenticación de las sesiones
 */

session_start();

echo "🔐 VERIFICACIÓN DE AUTENTICACIÓN DE SESIONES\n";
echo "=============================================\n\n";

echo "1. Estado de las sesiones PHP:\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session Status: " . session_status() . "\n";

echo "\n2. Variables de sesión de ZarzaPoints:\n";
$sessionVars = [
    'user_authenticated' => $_SESSION['user_authenticated'] ?? 'NO ESTABLECIDA',
    'user_id' => $_SESSION['user_id'] ?? 'NO ESTABLECIDA',
    'user_nombre' => $_SESSION['user_nombre'] ?? 'NO ESTABLECIDA',
    'user_email' => $_SESSION['user_email'] ?? 'NO ESTABLECIDA',
    'user_rol' => $_SESSION['user_rol'] ?? 'NO ESTABLECIDA',
    'user_puntos' => $_SESSION['user_puntos'] ?? 'NO ESTABLECIDA',
];

foreach ($sessionVars as $key => $value) {
    $status = ($value === 'NO ESTABLECIDA') ? '❌' : '✅';
    echo "   $status $key: $value\n";
}

echo "\n3. Todas las variables de sesión:\n";
if (empty($_SESSION)) {
    echo "   ⚠️  No hay variables de sesión establecidas\n";
} else {
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "   • $key: " . json_encode($value) . "\n";
        } else {
            echo "   • $key: $value\n";
        }
    }
}

echo "\n4. Estado de cookies:\n";
if (empty($_COOKIE)) {
    echo "   ⚠️  No hay cookies establecidas\n";
} else {
    foreach ($_COOKIE as $key => $value) {
        if (strpos($key, 'laravel') !== false || strpos($key, 'XSRF') !== false || strpos($key, 'session') !== false) {
            echo "   • $key: " . substr($value, 0, 20) . "...\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

// Verificar si la autenticación funciona
if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
    echo "✅ USUARIO AUTENTICADO CORRECTAMENTE\n";
    echo "   Usuario: " . ($_SESSION['user_nombre'] ?? 'N/A') . "\n";
    echo "   Email: " . ($_SESSION['user_email'] ?? 'N/A') . "\n";
    echo "   Rol: " . ($_SESSION['user_rol'] ?? 'N/A') . "\n";
    echo "   ✅ Debería poder acceder a las rutas protegidas\n";
} else {
    echo "❌ USUARIO NO AUTENTICADO\n";
    echo "   ⚠️  Será redirigido al login en rutas protegidas\n";
    echo "\n🔧 SOLUCIONES POSIBLES:\n";
    echo "   1. Iniciar sesión desde la interfaz web\n";
    echo "   2. Verificar que las cookies estén habilitadas\n";
    echo "   3. Verificar que el dominio sea correcto\n";
}

echo "\n5. Test de URL de tickets:\n";
$testUrls = [
    'http://localhost:8000/tickets',
    'http://localhost:8000/tickets/create',
    'http://localhost:8000/login'
];

foreach ($testUrls as $url) {
    echo "   • $url\n";
}

echo "\n💡 RECOMENDACIÓN:\n";
echo "   Accede a http://localhost:8000/login primero\n";
echo "   Luego prueba http://localhost:8000/tickets\n";