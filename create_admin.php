<?php
// Script para crear un nuevo usuario admin temporal
echo "=== CREANDO USUARIO ADMIN TEMPORAL ===\n";

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // Verificar si el usuario ya existe
    $checkStmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $checkStmt->execute(['admin@zarza.com']);
    
    if ($checkStmt->fetch()) {
        echo "✅ Usuario admin@zarza.com ya existe\n";
    } else {
        // Crear nuevo usuario admin
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('
            INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            RETURNING id
        ');
        
        $stmt->execute([
            'Admin',
            'Sistema',
            'admin@zarza.com',
            $password_hash,
            'admin'
        ]);
        
        $userId = $stmt->fetchColumn();
        
        // Crear registro de puntos inicial
        $puntosStmt = $pdo->prepare('
            INSERT INTO puntos (usuario_id, saldo, updated_at)
            VALUES (?, 1000, NOW())
        ');
        $puntosStmt->execute([$userId]);
        
        echo "✅ Usuario admin@zarza.com creado exitosamente\n";
        echo "   ID: $userId\n";
        echo "   Puntos iniciales: 1000\n";
    }
    
    // Verificar password
    $verifyStmt = $pdo->prepare('SELECT password FROM usuarios WHERE email = ?');
    $verifyStmt->execute(['admin@zarza.com']);
    $stored_hash = $verifyStmt->fetchColumn();
    
    if (password_verify('admin123', $stored_hash)) {
        echo "✅ Password verification: VALID\n";
    } else {
        echo "❌ Password verification: INVALID\n";
    }
    
    echo "\n=== CREDENCIALES ALTERNATIVAS ===\n";
    echo "Email: admin@zarza.com\n";
    echo "Password: admin123\n";
    echo "Rol: admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>