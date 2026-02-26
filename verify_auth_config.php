<?php
/**
 * Script para verificar configuración de autenticación
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFICANDO CONFIGURACIÓN DE AUTENTICACIÓN\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Verificar configuración del provider
$authConfig = config('auth');
echo "1. Configuración del Provider:\n";
echo "   Guard por defecto: " . $authConfig['defaults']['guard'] . "\n";
echo "   Provider: " . $authConfig['guards']['web']['provider'] . "\n";
echo "   Modelo: " . $authConfig['providers']['users']['model'] . "\n\n";

// 2. Verificar que el modelo existe y tiene la tabla correcta
echo "2. Verificando Modelo Usuario:\n";
try {
    $usuarioClass = $authConfig['providers']['users']['model'];
    $usuario = new $usuarioClass();
    echo "   ✅ Modelo existe: " . $usuarioClass . "\n";
    echo "   ✅ Tabla configurada: " . $usuario->getTable() . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Error con el modelo: " . $e->getMessage() . "\n\n";
}

// 3. Verificar conexión a la tabla correcta
echo "3. Verificando conexión a BD:\n";
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // Verificar tabla usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();
    echo "   ✅ Tabla 'usuarios' existe\n";
    echo "   ✅ Total de usuarios: $count\n\n";
    
    // Verificar tabla users (no debería existir)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        echo "   ⚠️  Tabla 'users' también existe (no es necesaria)\n\n";
    } catch (Exception $e) {
        echo "   ✅ Tabla 'users' no existe (correcto)\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error de BD: " . $e->getMessage() . "\n\n";
}

// 4. Probar Auth::check() con un usuario
echo "4. Probando Auth con usuario de prueba:\n";
try {
    // Buscar usuario admin
    $usuario = \App\Models\Usuario::where('email', 'admin@test.com')->first();
    
    if ($usuario) {
        echo "   ✅ Usuario encontrado: " . $usuario->email . "\n";
        echo "   ✅ ID: " . $usuario->id . "\n";
        echo "   ✅ Nombre: " . $usuario->nombres . "\n";
        echo "   ✅ Rol: " . $usuario->rol . "\n\n";
        
        // Simular login
        \Illuminate\Support\Facades\Auth::login($usuario);
        
        if (\Illuminate\Support\Facades\Auth::check()) {
            echo "   ✅ Auth::check() funciona correctamente\n";
            echo "   ✅ Usuario autenticado: " . \Illuminate\Support\Facades\Auth::user()->email . "\n";
        } else {
            echo "   ❌ Auth::check() falló\n";
        }
        
        // Logout
        \Illuminate\Support\Facades\Auth::logout();
        echo "   ✅ Logout exitoso\n\n";
    } else {
        echo "   ⚠️  Usuario admin@test.com no encontrado\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error al probar Auth: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "✅ VERIFICACIÓN COMPLETADA\n\n";

echo "📋 RESUMEN:\n";
echo "• Laravel ahora usa el modelo 'Usuario' en lugar de 'User'\n";
echo "• La tabla configurada es 'usuarios'\n";
echo "• Auth::check() debería funcionar correctamente\n";
echo "• Recuerda hacer login nuevamente en el navegador\n";
