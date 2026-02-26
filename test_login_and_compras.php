<?php
/**
 * Script para probar el login y verificar acceso a compras
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔐 PRUEBA DE LOGIN Y ACCESO A COMPRAS\n";
    echo "====================================\n\n";
    
    // 1. Verificar usuarios existentes
    echo "1. Verificando usuarios disponibles...\n";
    $stmt = $pdo->query("SELECT id, nombres, email, password, rol FROM usuarios WHERE email IN ('cliente@test.com', 'admin@test.com')");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "   ❌ No hay usuarios de prueba\n";
        echo "   🔧 Creando usuarios...\n";
        
        // Cliente
        $pdo->exec("
            INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at)
            VALUES ('Cliente', 'Prueba', 'cliente@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NOW(), NOW())
            ON CONFLICT (email) DO NOTHING
        ");
        
        // Admin
        $pdo->exec("
            INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at)
            VALUES ('Admin', 'Sistema', 'admin@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW())
            ON CONFLICT (email) DO NOTHING
        ");
        
        $stmt = $pdo->query("SELECT id, nombres, email, password, rol FROM usuarios WHERE email IN ('cliente@test.com', 'admin@test.com')");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    foreach ($users as $user) {
        echo "   ✅ {$user['email']} ({$user['rol']}) - ID: {$user['id']}\n";
        echo "      Hash: " . substr($user['password'], 0, 20) . "...\n";
    }
    
    // 2. Probar validación de contraseña
    echo "\n2. Probando validación de contraseñas...\n";
    $testPassword = 'password';
    
    foreach ($users as $user) {
        $isValid = password_verify($testPassword, $user['password']);
        $status = $isValid ? '✅' : '❌';
        echo "   $status {$user['email']} con password '$testPassword': " . ($isValid ? 'VÁLIDA' : 'INVÁLIDA') . "\n";
    }
    
    // 3. Simular proceso de login
    echo "\n3. Simulando proceso de login...\n";
    
    session_start();
    
    // Limpiar sesión anterior
    session_unset();
    session_destroy();
    session_start();
    
    echo "   🔧 Sesión limpia iniciada\n";
    
    // Simular login exitoso con usuario cliente
    $clientUser = null;
    foreach ($users as $user) {
        if ($user['email'] === 'cliente@test.com') {
            $clientUser = $user;
            break;
        }
    }
    
    if ($clientUser) {
        // Establecer variables de sesión como lo haría el AuthController
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $clientUser['id'];
        $_SESSION['user_nombre'] = $clientUser['nombres'];
        $_SESSION['user_apellido'] = $clientUser['apellido_paterno'] ?? '';
        $_SESSION['user_email'] = $clientUser['email'];
        $_SESSION['user_rol'] = $clientUser['rol'];
        
        // Obtener puntos del usuario
        $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
        $stmt->execute([$clientUser['id']]);
        $puntos = $stmt->fetchColumn() ?: 0;
        $_SESSION['user_puntos'] = $puntos;
        
        echo "   ✅ Login simulado para: {$clientUser['email']}\n";
        echo "   ✅ Variables de sesión establecidas:\n";
        echo "      - user_authenticated: " . ($_SESSION['user_authenticated'] ? 'true' : 'false') . "\n";
        echo "      - user_id: {$_SESSION['user_id']}\n";
        echo "      - user_email: {$_SESSION['user_email']}\n";
        echo "      - user_rol: {$_SESSION['user_rol']}\n";
        echo "      - user_puntos: {$_SESSION['user_puntos']}\n";
    }
    
    // 4. Verificar middleware
    echo "\n4. Verificando middleware de autenticación...\n";
    
    if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
        echo "   ✅ Middleware pasaría: Usuario autenticado\n";
        echo "   ✅ Acceso permitido a rutas protegidas\n";
    } else {
        echo "   ❌ Middleware fallaría: Usuario NO autenticado\n";
        echo "   ❌ Sería redirigido al login\n";
    }
    
    // 5. Probar acceso a la función de compras
    echo "\n5. Probando acceso a función de compras...\n";
    
    // Verificar si existen compras para el usuario
    if ($clientUser) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_compras,
                   COALESCE(SUM(monto), 0) as monto_total,
                   COALESCE(SUM(puntos_generados), 0) as puntos_total
            FROM compras 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$clientUser['id']]);
        $comprasData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   📊 Estadísticas del usuario:\n";
        echo "      - Total compras: {$comprasData['total_compras']}\n";
        echo "      - Monto total: \${$comprasData['monto_total']}\n";
        echo "      - Puntos por compras: {$comprasData['puntos_total']}\n";
        
        if ($comprasData['total_compras'] > 0) {
            echo "   ✅ El usuario tiene compras para mostrar\n";
        } else {
            echo "   ⚠️  El usuario no tiene compras registradas\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📋 INSTRUCCIONES PARA ACCEDER A COMPRAS\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "🔐 PASOS PARA LOGIN MANUAL:\n";
    echo "1. Ve a: http://localhost:8000/login\n";
    echo "2. Credenciales del cliente:\n";
    echo "   📧 Email: cliente@test.com\n";
    echo "   🔑 Password: password\n";
    echo "3. Credenciales del admin:\n";
    echo "   📧 Email: admin@test.com\n";
    echo "   🔑 Password: password\n";
    echo "4. Después del login, ve a: http://localhost:8000/compras\n\n";
    
    echo "🔍 VERIFICACIÓN DE PROBLEMAS:\n";
    echo "• Si sigue redirigiendo al login después de autenticarte:\n";
    echo "  1. Verifica que uses exactamente http://localhost:8000\n";
    echo "  2. Abre herramientas de desarrollo (F12)\n";
    echo "  3. Ve a la pestaña 'Application' o 'Storage'\n";
    echo "  4. Verifica que existan cookies de sesión\n";
    echo "  5. Prueba en modo incógnito\n\n";
    
    echo "🌐 URLs DE PRUEBA:\n";
    echo "• http://localhost:8000/login - Página de login\n";
    echo "• http://localhost:8000/compras - Página de compras (requiere auth)\n";
    echo "• http://localhost:8000/tickets - Página de tickets (requiere auth)\n";
    echo "• http://localhost:8000/ - Dashboard principal\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}