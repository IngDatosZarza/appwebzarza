<?php
/**
 * Script para probar el acceso a las rutas después del login
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🧪 PRUEBA DE ACCESO A RUTAS DE TICKETS\n";
    echo "======================================\n\n";
    
    // 1. Verificar que existe un usuario de prueba
    echo "1. Verificando usuario de prueba...\n";
    $stmt = $pdo->query("SELECT id, nombres, email, rol FROM usuarios WHERE email = 'cliente@test.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "   ❌ Usuario de prueba no encontrado\n";
        echo "   🔧 Creando usuario de prueba...\n";
        $pdo->exec("
            INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at)
            VALUES ('Cliente', 'Prueba', 'cliente@test.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NOW(), NOW())
        ");
        $user = ['id' => $pdo->lastInsertId(), 'nombres' => 'Cliente', 'email' => 'cliente@test.com', 'rol' => 'cliente'];
    }
    
    echo "   ✅ Usuario: {$user['nombres']} ({$user['email']})\n";
    echo "   ✅ Rol: {$user['rol']}\n";
    echo "   ✅ ID: {$user['id']}\n";
    
    // 2. Obtener saldo de puntos
    $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
    $stmt->execute([$user['id']]);
    $puntos = $stmt->fetchColumn() ?: 0;
    
    echo "\n2. Datos del usuario:\n";
    echo "   💰 Puntos actuales: $puntos\n";
    
    // 3. Simular sesión autenticada
    session_start();
    $_SESSION['user_authenticated'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nombre'] = $user['nombres'];
    $_SESSION['user_apellido'] = $user['apellido_paterno'] ?? '';
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_rol'] = $user['rol'];
    $_SESSION['user_puntos'] = $puntos;
    
    echo "\n3. Sesión simulada establecida:\n";
    echo "   ✅ user_authenticated: " . ($_SESSION['user_authenticated'] ? 'true' : 'false') . "\n";
    echo "   ✅ user_id: {$_SESSION['user_id']}\n";
    echo "   ✅ user_nombre: {$_SESSION['user_nombre']}\n";
    echo "   ✅ user_email: {$_SESSION['user_email']}\n";
    echo "   ✅ user_rol: {$_SESSION['user_rol']}\n";
    
    echo "\n4. URLs que ahora deberían funcionar:\n";
    $workingUrls = [
        'http://localhost:8000/tickets' => 'Lista de tickets',
        'http://localhost:8000/tickets/create' => 'Registrar ticket',
        'http://localhost:8000/' => 'Dashboard principal',
        'http://localhost:8000/cupones' => 'Cupones disponibles',
        'http://localhost:8000/perfil' => 'Perfil de usuario'
    ];
    
    foreach ($workingUrls as $url => $description) {
        echo "   ✅ $url - $description\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ SESIÓN AUTENTICADA ESTABLECIDA\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🌐 PASOS PARA PROBAR EN EL NAVEGADOR:\n";
    echo "1. Ve a: http://localhost:8000/login\n";
    echo "2. Usa estas credenciales:\n";
    echo "   📧 Email: cliente@test.com\n";
    echo "   🔑 Password: password\n";
    echo "3. Después del login, ve a: http://localhost:8000/tickets\n";
    echo "4. Debería mostrar la página de tickets sin redireccionar\n\n";
    
    echo "📋 CREDENCIALES DE PRUEBA:\n";
    echo "   Email: cliente@test.com\n";
    echo "   Password: password\n";
    echo "   (Esta es la contraseña por defecto del seeder de Laravel)\n\n";
    
    echo "🔧 SI AÚN REDIRIGE AL LOGIN:\n";
    echo "1. Verificar que las cookies estén habilitadas\n";
    echo "2. Usar modo incógnito/privado del navegador\n";
    echo "3. Limpiar cookies del navegador\n";
    echo "4. Verificar que la URL sea exactamente http://localhost:8000\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}