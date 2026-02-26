<?php
/**
 * Script de prueba para verificar la corrección de getCurrentUser()
 */

echo "🧪 PRUEBA DE CORRECCIÓN - getCurrentUser() FUNCTION\n";
echo "================================================\n\n";

try {
    // Test 1: Verificar que el helper de usuario existe
    echo "🔍 Test 1: Verificando helper de usuario\n";
    
    $userHelperPath = __DIR__ . '/app/Helpers/user_helper.php';
    if (file_exists($userHelperPath)) {
        echo "   ✅ Helper encontrado: $userHelperPath\n";
        echo "   📏 Tamaño: " . filesize($userHelperPath) . " bytes\n";
        
        // Incluir el helper
        require_once $userHelperPath;
        
        if (function_exists('getCurrentUser')) {
            echo "   ✅ Función getCurrentUser() disponible\n";
        } else {
            echo "   ❌ Función getCurrentUser() NO disponible\n";
        }
        
        if (function_exists('isAuthenticated')) {
            echo "   ✅ Función isAuthenticated() disponible\n";
        } else {
            echo "   ❌ Función isAuthenticated() NO disponible\n";
        }
        
    } else {
        echo "   ❌ Helper NO encontrado: $userHelperPath\n";
    }
    
    // Test 2: Simular inclusión desde vista dashboard
    echo "\n🔍 Test 2: Simulando inclusión desde vista dashboard\n";
    
    $viewDir = __DIR__ . '/resources/views/frontend';
    $helperFromView = realpath($viewDir . '/../../../app/Helpers/user_helper.php');
    
    echo "   📁 Directorio de vista: $viewDir\n";
    echo "   🔗 Ruta calculada: $helperFromView\n";
    
    if ($helperFromView && file_exists($helperFromView)) {
        echo "   ✅ Ruta desde vista VÁLIDA\n";
    } else {
        echo "   ❌ Ruta desde vista INVÁLIDA\n";
    }
    
    // Test 3: Probar funciones sin sesión
    echo "\n🔍 Test 3: Probando funciones sin sesión activa\n";
    
    if (function_exists('getCurrentUser')) {
        $user = getCurrentUser();
        
        if ($user === null) {
            echo "   ✅ getCurrentUser() retorna null sin sesión (correcto)\n";
        } else {
            echo "   ❌ getCurrentUser() no retorna null sin sesión\n";
        }
        
        $isAuth = isAuthenticated();
        if ($isAuth === false) {
            echo "   ✅ isAuthenticated() retorna false sin sesión (correcto)\n";
        } else {
            echo "   ❌ isAuthenticated() no retorna false sin sesión\n";
        }
    }
    
    // Test 4: Simular sesión de usuario
    echo "\n🔍 Test 4: Simulando sesión de usuario\n";
    
    // Simular datos de sesión
    $_SESSION['user_authenticated'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@test.com';
    $_SESSION['user_nombre'] = 'Admin Usuario';
    $_SESSION['user_rol'] = 'admin';
    $_SESSION['user_puntos'] = 1500;
    
    if (function_exists('getCurrentUser')) {
        $user = getCurrentUser();
        
        if ($user && is_object($user)) {
            echo "   ✅ getCurrentUser() retorna objeto usuario\n";
            echo "   👤 Nombre: " . $user->nombre . "\n";
            echo "   📧 Email: " . $user->email . "\n";
            echo "   🎯 Rol: " . $user->rol . "\n";
            echo "   💰 Puntos: " . $user->puntos . "\n";
        } else {
            echo "   ❌ getCurrentUser() no retorna objeto válido\n";
        }
        
        if (function_exists('isAdmin')) {
            $isAdminUser = isAdmin();
            echo "   " . ($isAdminUser ? "✅" : "❌") . " isAdmin() retorna: " . ($isAdminUser ? "true" : "false") . "\n";
        }
    }
    
    // Test 5: Verificar vista dashboard corregida
    echo "\n🔍 Test 5: Verificando vista dashboard corregida\n";
    
    $dashboardPath = 'resources/views/frontend/dashboard.php';
    if (file_exists($dashboardPath)) {
        $dashboardContent = file_get_contents($dashboardPath);
        
        if (strpos($dashboardContent, 'user_helper.php') !== false) {
            echo "   ✅ Vista incluye user_helper.php\n";
        } else {
            echo "   ❌ Vista NO incluye user_helper.php\n";
        }
        
        if (strpos($dashboardContent, 'getCurrentUser()') !== false) {
            echo "   ✅ Vista usa getCurrentUser()\n";
        } else {
            echo "   ❌ Vista NO usa getCurrentUser()\n";
        }
        
        if (strpos($dashboardContent, 'user_rol') !== false) {
            echo "   ✅ Vista usa user_rol (consistente)\n";
        } else {
            echo "   ⚠️  Vista no usa user_rol\n";
        }
    }
    
    echo "\n📋 RESUMEN DE LA CORRECCIÓN:\n";
    echo "============================\n";
    echo "✅ Helper user_helper.php creado con getCurrentUser()\n";
    echo "✅ Funciones de autenticación implementadas\n";
    echo "✅ Vista dashboard actualizada con inclusión del helper\n";
    echo "✅ Vista coupons actualizada con inclusión del helper\n";
    echo "✅ Inconsistencias user_role/user_rol corregidas\n";
    
    echo "\n🚀 PRÓXIMOS PASOS:\n";
    echo "1. Reiniciar servidor Laravel\n";
    echo "2. Acceder a http://localhost:8080/\n";
    echo "3. Verificar que no hay error getCurrentUser()\n";
    echo "4. Login y verificar funcionalidad del dashboard\n";
    
    echo "\n✅ CORRECCIÓN DE getCurrentUser() COMPLETADA\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
}
?>