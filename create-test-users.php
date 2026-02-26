<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');

    // Verificar si ya existen
    $check = $pdo->query("SELECT email FROM usuarios WHERE email IN ('cliente@test.com', 'admin@test.com')");
    if ($check->rowCount() > 0) {
        echo "Usuarios de prueba ya existen\n";
        exit;
    }

    // Crear usuario cliente
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id');
    $stmt->execute(['Cliente', 'Prueba', 'cliente@test.com', $hashedPassword, 'cliente']);
    $clienteId = $stmt->fetchColumn();

    $puntosStmt = $pdo->prepare('INSERT INTO puntos (usuario_id, saldo, updated_at) VALUES (?, 250, NOW())');
    $puntosStmt->execute([$clienteId]);

    // Crear usuario admin
    $hashedPasswordAdmin = password_hash('admin123', PASSWORD_DEFAULT);
    $adminStmt = $pdo->prepare('INSERT INTO usuarios (nombres, apellido_paterno, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id');
    $adminStmt->execute(['Admin', 'Sistema', 'admin@test.com', $hashedPasswordAdmin, 'admin']);
    $adminId = $adminStmt->fetchColumn();

    $puntosAdminStmt = $pdo->prepare('INSERT INTO puntos (usuario_id, saldo, updated_at) VALUES (?, 1000, NOW())');
    $puntosAdminStmt->execute([$adminId]);

    echo "Usuarios de prueba creados exitosamente:\n";
    echo "- cliente@test.com / password123 (250 puntos)\n";
    echo "- admin@test.com / admin123 (1000 puntos)\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>