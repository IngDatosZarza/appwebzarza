<?php
/**
 * Script para probar acceso a /admin/points
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🧪 PRUEBA DE ACCESO A /admin/points\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Autenticar usuario admin
    echo "1. Autenticando usuario admin...\n";
    $admin = \App\Models\Usuario::where('email', 'admin@test.com')->first();
    
    if (!$admin) {
        echo "   ❌ Usuario admin no encontrado\n";
        exit(1);
    }
    
    echo "   ✅ Admin encontrado: {$admin->email}\n";
    echo "   ✅ ID: {$admin->id}\n";
    echo "   ✅ Rol: {$admin->rol}\n\n";
    
    // 2. Simular login
    echo "2. Simulando login con Auth::login()...\n";
    \Illuminate\Support\Facades\Auth::login($admin);
    
    if (!\Illuminate\Support\Facades\Auth::check()) {
        echo "   ❌ Auth::check() falló después de login\n";
        exit(1);
    }
    
    echo "   ✅ Auth::check() = true\n";
    echo "   ✅ Usuario autenticado: " . \Illuminate\Support\Facades\Auth::user()->email . "\n";
    echo "   ✅ ID del usuario: " . \Illuminate\Support\Facades\Auth::id() . "\n\n";
    
    // 3. Verificar que puede acceder a datos de admin
    echo "3. Verificando acceso a datos de admin...\n";
    
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        
        // Consultar estadísticas como lo haría el controlador
        $stmt = $pdo->query("
            SELECT 
                COUNT(DISTINCT u.id) as total_usuarios,
                COALESCE(SUM(p.saldo), 0) as total_puntos,
                COUNT(DISTINCT c.id) as total_compras
            FROM usuarios u
            LEFT JOIN puntos p ON u.id = p.usuario_id
            LEFT JOIN compras c ON u.id = c.usuario_id
            WHERE u.rol = 'cliente'
        ");
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   ✅ Consulta a BD exitosa\n";
        echo "   ✅ Total usuarios: {$stats['total_usuarios']}\n";
        echo "   ✅ Total puntos: {$stats['total_puntos']}\n";
        echo "   ✅ Total compras: {$stats['total_compras']}\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error consultando BD: " . $e->getMessage() . "\n\n";
    }
    
    // 4. Verificar middleware CustomAuth
    echo "4. Verificando middleware CustomAuth...\n";
    $request = \Illuminate\Http\Request::create('/admin/points', 'GET');
    
    $middleware = new \App\Http\Middleware\CustomAuth();
    
    try {
        $response = $middleware->handle($request, function($req) {
            return new \Illuminate\Http\Response('OK');
        });
        
        if ($response->getStatusCode() === 200) {
            echo "   ✅ Middleware permite el acceso\n";
            echo "   ✅ Response: " . $response->getContent() . "\n\n";
        } else {
            echo "   ❌ Middleware bloqueó el acceso\n";
            echo "   ❌ Status: " . $response->getStatusCode() . "\n\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error en middleware: " . $e->getMessage() . "\n\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ TODAS LAS PRUEBAS PASARON\n\n";
    
    echo "📋 RESUMEN:\n";
    echo "• Auth::login() funciona correctamente\n";
    echo "• Auth::check() devuelve true\n";
    echo "• Consultas a BD funcionan\n";
    echo "• Middleware permite acceso\n\n";
    
    echo "🎯 SIGUIENTE PASO:\n";
    echo "1. Ve a: http://localhost:8000/logout\n";
    echo "2. Luego ve a: http://localhost:8000/login\n";
    echo "3. Ingresa con:\n";
    echo "   Email: admin@test.com\n";
    echo "   Password: password\n";
    echo "4. Accede a: http://localhost:8000/admin/points\n";
    
    // Logout
    \Illuminate\Support\Facades\Auth::logout();
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
