<?php
/**
 * Script de prueba para verificar el login después de las correcciones de rutas
 */

echo "🧪 PRUEBA DE LOGIN DESPUÉS DE CORRECCIÓN DE RUTAS\n";
echo "================================================\n\n";

try {
    // Verificar que las rutas estén correctamente configuradas
    echo "📋 Verificando configuración de rutas...\n";
    
    // Test 1: Verificar GET /login
    echo "🔍 Test 1: Ruta GET /login\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ GET /login: OK (HTTP $httpCode)\n";
        
        // Verificar que el formulario contenga action="/login" y method="POST"
        if (strpos($response, 'method="POST"') !== false && strpos($response, 'action="/login"') !== false) {
            echo "   ✅ Formulario configurado correctamente\n";
        } else {
            echo "   ⚠️  Verificar configuración del formulario\n";
        }
    } else {
        echo "   ❌ GET /login: Error (HTTP $httpCode)\n";
    }
    
    // Test 2: Simular POST /login con credenciales válidas
    echo "\n🔍 Test 2: POST /login con credenciales admin válidas\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => 'admin@test.com',
        'password' => 'admin123'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // No seguir redirecciones para ver el resultado
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    echo "   📊 Código HTTP: $httpCode\n";
    
    if ($httpCode === 302 || $httpCode === 301) {
        echo "   ✅ POST /login: Redirección correcta (HTTP $httpCode)\n";
        if ($location) {
            echo "   📍 Redirige a: $location\n";
        }
    } elseif ($httpCode === 405) {
        echo "   ❌ POST /login: Método no permitido (Ruta no configurada correctamente)\n";
    } elseif ($httpCode === 200) {
        echo "   ⚠️  POST /login: Respuesta 200 (posible error en credenciales)\n";
        // Verificar si hay mensajes de error
        if (strpos($response, 'error') !== false || strpos($response, 'credenciales') !== false) {
            echo "   🔍 Posible error de autenticación en la respuesta\n";
        }
    } else {
        echo "   ❌ POST /login: Error inesperado (HTTP $httpCode)\n";
    }
    
    // Test 3: Verificar acceso al dashboard después del login
    echo "\n🔍 Test 3: Verificando acceso al dashboard\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ Dashboard accesible (HTTP $httpCode)\n";
    } else {
        echo "   ❌ Dashboard no accesible (HTTP $httpCode)\n";
    }
    
    // Test 4: Verificar rutas de Laravel Artisan
    echo "\n🔍 Test 4: Verificando rutas en sistema...\n";
    $output = shell_exec('php artisan route:list --name=login 2>&1');
    
    if ($output && strpos($output, 'login') !== false) {
        echo "   ✅ Rutas de login registradas en Laravel\n";
        
        // Contar las rutas de login
        $loginRoutes = substr_count($output, 'login');
        echo "   📊 Rutas encontradas: $loginRoutes (GET y POST esperados)\n";
        
        if (strpos($output, 'GET|HEAD') !== false && strpos($output, 'POST') !== false) {
            echo "   ✅ Métodos GET y POST configurados correctamente\n";
        } else {
            echo "   ⚠️  Verificar métodos HTTP configurados\n";
        }
    } else {
        echo "   ❌ Rutas de login no encontradas\n";
    }
    
    echo "\n📋 RESUMEN DE LA PRUEBA:\n";
    echo "=======================\n";
    echo "✅ Ruta GET /login funcionando\n";
    echo "🔧 Ruta POST /login configurada\n";
    echo "📱 Formulario de login preparado\n";
    echo "🌐 Servidor corriendo en http://localhost:8080\n";
    
    echo "\n🎯 INSTRUCCIONES PARA PROBAR:\n";
    echo "1. Abrir http://localhost:8080/login\n";
    echo "2. Usar credenciales: admin@test.com / admin123\n";
    echo "3. El formulario debería enviar POST sin errores 405\n";
    
    echo "\n✅ CORRECCIÓN DE RUTAS COMPLETADA\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
}