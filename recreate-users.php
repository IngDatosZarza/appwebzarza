<?php
// Configuración de conexión
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';
$username = 'appwebuser';
$password_db = 'appwebpass';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("SET search_path TO appweb, public");
    
    echo "=== USUARIOS ACTUALES ===\n";
    
    $stmt = $pdo->query('SELECT id, nombres, apellido_paterno, email, rol FROM usuarios ORDER BY id');
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "No hay usuarios en la base de datos\n";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']} | {$user['nombres']} {$user['apellido_paterno']} | {$user['email']} | Rol: {$user['rol']}\n";
        }
    }
    
    echo "\n=== RECREANDO USUARIOS DE PRUEBA ===\n";
    
    // Eliminar usuarios existentes
    $deleteStmt = $pdo->prepare('DELETE FROM puntos WHERE usuario_id IN (SELECT id FROM usuarios WHERE email IN (?, ?))');
    $deleteStmt->execute(['cliente@test.com', 'admin@test.com']);
    
    $deleteUsersStmt = $pdo->prepare('DELETE FROM usuarios WHERE email IN (?, ?)');
    $deleteUsersStmt->execute(['cliente@test.com', 'admin@test.com']);
    
    echo "✅ Usuarios anteriores eliminados\n";
    
    // Crear usuario cliente con contraseña correcta
    $hashedPasswordCliente = password_hash('cliente123', PASSWORD_DEFAULT);
    $clienteStmt = $pdo->prepare('INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id');
    $clienteStmt->execute(['Cliente', 'Prueba', 'cliente@test.com', $hashedPasswordCliente, 'cliente']);
    $clienteId = $clienteStmt->fetchColumn();
    
    $puntosStmt = $pdo->prepare('INSERT INTO puntos (usuario_id, saldo, updated_at) VALUES (?, 250, NOW())');
    $puntosStmt->execute([$clienteId]);
    
    echo "✅ Usuario cliente creado (ID: $clienteId)\n";
    
    // Crear usuario admin con contraseña correcta
    $hashedPasswordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
    $adminStmt = $pdo->prepare('INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id');
    $adminStmt->execute(['Admin', 'Sistema', 'admin@test.com', $hashedPasswordAdmin, 'admin']);
    $adminId = $adminStmt->fetchColumn();
    
    $puntosAdminStmt = $pdo->prepare('INSERT INTO puntos (usuario_id, saldo, updated_at) VALUES (?, 1000, NOW())');
    $puntosAdminStmt->execute([$adminId]);
    
    echo "✅ Usuario admin creado (ID: $adminId)\n";
    echo "\n=== CREDENCIALES DE PRUEBA ===\n";
    echo "Cliente: cliente@test.com / cliente123\n";
    echo "Admin: admin@test.com / admin123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>