<?php
// Script para crear/actualizar usuarios de prueba con contraseñas conocidas

echo "=== CREANDO USUARIOS DE PRUEBA ===\n";

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // Contraseñas conocidas
    $clientePassword = password_hash('password123', PASSWORD_DEFAULT);
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "1. Generando hashes de contraseñas...\n";
    echo "   Cliente hash: " . substr($clientePassword, 0, 30) . "...\n";
    echo "   Admin hash: " . substr($adminPassword, 0, 30) . "...\n";
    
    // Actualizar/crear usuario cliente
    echo "2. Actualizando usuario cliente@test.com...\n";
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, password, telefono, rol, created_at, updated_at)
        VALUES ('Cliente', 'Prueba', 'Test', 'cliente@test.com', ?, '1234567890', 'cliente', NOW(), NOW())
        ON CONFLICT (email) DO UPDATE SET 
            password = EXCLUDED.password,
            updated_at = NOW()
    ");
    $stmt->execute([$clientePassword]);
    echo "✅ Usuario cliente actualizado\n";
    
    // Actualizar/crear usuario admin
    echo "3. Actualizando usuario admin@test.com...\n";
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, password, telefono, rol, created_at, updated_at)
        VALUES ('Admin', 'Sistema', 'Test', 'admin@test.com', ?, '0987654321', 'admin', NOW(), NOW())
        ON CONFLICT (email) DO UPDATE SET 
            password = EXCLUDED.password,
            updated_at = NOW()
    ");
    $stmt->execute([$adminPassword]);
    echo "✅ Usuario admin actualizado\n";
    
    // Verificar que los usuarios tienen registros de puntos
    echo "4. Verificando registros de puntos...\n";
    
    $stmt = $pdo->query("SELECT u.id, u.email FROM usuarios u WHERE u.email IN ('cliente@test.com', 'admin@test.com')");
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $puntosStmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
        $puntosStmt->execute([$user['id']]);
        $puntos = $puntosStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$puntos) {
            echo "   Creando registro de puntos para {$user['email']}...\n";
            $insertPuntos = $pdo->prepare("INSERT INTO puntos (usuario_id, saldo, updated_at) VALUES (?, 0, NOW())");
            $insertPuntos->execute([$user['id']]);
        } else {
            echo "   {$user['email']} ya tiene puntos: {$puntos['saldo']}\n";
        }
    }
    
    echo "\n5. Probando login con nuevas contraseñas...\n";
    
    // Probar login cliente
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = 'cliente@test.com'");
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('password123', $cliente['password'])) {
        echo "✅ Login cliente@test.com con password123: CORRECTO\n";
    } else {
        echo "❌ Login cliente@test.com con password123: FALLO\n";
    }
    
    // Probar login admin
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = 'admin@test.com'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify('admin123', $admin['password'])) {
        echo "✅ Login admin@test.com con admin123: CORRECTO\n";
    } else {
        echo "❌ Login admin@test.com con admin123: FALLO\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== USUARIOS DE PRUEBA LISTOS ===\n";
?>