<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== CREANDO USUARIO CLIENTE DE PRUEBA ===\n";
    
    // Verificar si ya existe
    $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $checkStmt->execute(['cliente.prueba@test.com']);
    
    if ($checkStmt->fetchColumn()) {
        echo "✅ Usuario cliente.prueba@test.com ya existe\n";
    } else {
        // Crear usuario cliente
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, telefono, fecha_nacimiento, password, rol, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente', NOW(), NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            'María',
            'González',
            'López',
            'cliente.prueba@test.com',
            '5551234567',
            '1990-05-15',
            password_hash('123456', PASSWORD_DEFAULT)
        ]);
        
        $userId = $stmt->fetchColumn();
        
        // Crear registro de puntos
        $puntosStmt = $pdo->prepare("
            INSERT INTO puntos (usuario_id, saldo, updated_at)
            VALUES (?, 150, NOW())
        ");
        $puntosStmt->execute([$userId]);
        
        echo "✅ Usuario cliente creado: cliente.prueba@test.com / 123456\n";
        echo "✅ Asignados 150 puntos iniciales\n";
    }
    
    // Mostrar credenciales disponibles
    echo "\n=== CREDENCIALES DE PRUEBA ===\n";
    echo "👤 Admin: admin@zarza.com / admin123\n";
    echo "👤 Cliente: cliente.prueba@test.com / 123456\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>