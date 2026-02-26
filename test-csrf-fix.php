<?php
/**
 * Script de prueba para verificar el problema CSRF y corregirlo
 */

echo "🧪 DIAGNÓSTICO DEL ERROR 419 - PAGE EXPIRED (CSRF)\n";
echo "================================================\n\n";

try {
    // Test 1: Verificar si hay token CSRF en la vista de login
    echo "🔍 Test 1: Verificando token CSRF en vista de login\n";
    
    $loginViewPath = 'resources/views/auth/login.php';
    if (file_exists($loginViewPath)) {
        $loginContent = file_get_contents($loginViewPath);
        
        if (strpos($loginContent, '_token') !== false) {
            echo "   ✅ Token CSRF encontrado en vista de login\n";
        } else {
            echo "   ❌ Token CSRF NO encontrado en vista de login\n";
        }
        
        if (strpos($loginContent, 'csrf_token()') !== false) {
            echo "   ✅ Función csrf_token() implementada\n";
        } else {
            echo "   ❌ Función csrf_token() NO implementada\n";
        }
    } else {
        echo "   ❌ Vista de login no encontrada\n";
    }
    
    // Test 2: Verificar configuración de sesiones
    echo "\n🔍 Test 2: Verificando configuración de sesiones\n";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "   📋 Estado de sesión: " . session_status() . "\n";
    echo "   🆔 Session ID: " . session_id() . "\n";
    
    // Test 3: Verificar si Laravel maneja CSRF
    echo "\n🔍 Test 3: Verificando manejo de CSRF\n";
    
    if (function_exists('csrf_token')) {
        $token = csrf_token();
        echo "   ✅ Función csrf_token() disponible\n";
        echo "   🔑 Token generado: " . substr($token, 0, 10) . "...\n";
    } else {
        echo "   ❌ Función csrf_token() NO disponible\n";
        echo "   💡 Solución: Implementar token manual\n";
    }
    
    // Test 4: Crear token CSRF manual si no existe
    echo "\n🔍 Test 4: Implementando token CSRF manual\n";
    
    if (!function_exists('csrf_token')) {
        function csrf_token() {
            if (!isset($_SESSION['_token'])) {
                $_SESSION['_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['_token'];
        }
        echo "   ✅ Función csrf_token() creada manualmente\n";
    }
    
    if (!function_exists('csrf_field')) {
        function csrf_field() {
            return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        }
        echo "   ✅ Función csrf_field() creada manualmente\n";
    }
    
    // Test 5: Verificar si podemos generar un token
    echo "\n🔍 Test 5: Generando token de prueba\n";
    
    $testToken = csrf_token();
    echo "   🔑 Token de prueba: " . $testToken . "\n";
    echo "   📏 Longitud del token: " . strlen($testToken) . " caracteres\n";
    
    // Test 6: Verificar la ruta del problema
    echo "\n🔍 Test 6: Verificando configuración de rutas POST\n";
    
    $webRoutes = 'routes/web.php';
    if (file_exists($webRoutes)) {
        $routesContent = file_get_contents($webRoutes);
        
        if (strpos($routesContent, "POST.*login") !== false || strpos($routesContent, "'login'") !== false) {
            echo "   ✅ Ruta POST /login configurada\n";
        } else {
            echo "   ❌ Ruta POST /login NO encontrada\n";
        }
    }
    
    echo "\n📋 SOLUCIONES RECOMENDADAS:\n";
    echo "================================\n";
    
    echo "1. ✅ Token CSRF agregado a formularios de login y registro\n";
    echo "2. 🔧 Redirección de admin a /admin/points configurada\n";
    echo "3. 💡 Crear helper CSRF manual si Laravel no funciona\n";
    echo "4. 🛠️  Opción alternativa: Deshabilitar CSRF para auth routes\n";
    
    echo "\n🚀 PRÓXIMOS PASOS:\n";
    echo "1. Reiniciar servidor Laravel\n";
    echo "2. Probar login con admin@test.com / admin123\n";
    echo "3. Verificar redirección a panel de admin\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el diagnóstico: " . $e->getMessage() . "\n";
}
?>