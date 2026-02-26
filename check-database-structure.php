<?php
// Verificar estructura completa de la base de datos
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
    
    echo "=== ESTRUCTURA DE LA BASE DE DATOS ===\n\n";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'appweb' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "📋 TABLA: $table\n";
        echo str_repeat("-", 50) . "\n";
        
        // Obtener columnas de cada tabla
        $columnStmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default 
                                  FROM information_schema.columns 
                                  WHERE table_schema = 'appweb' AND table_name = '$table' 
                                  ORDER BY ordinal_position");
        $columns = $columnStmt->fetchAll();
        
        foreach ($columns as $column) {
            $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column['column_default'] ? " DEFAULT: {$column['column_default']}" : '';
            echo "  • {$column['column_name']} ({$column['data_type']}) $nullable$default\n";
        }
        
        // Contar registros
        $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $countStmt->fetchColumn();
        echo "  📊 Registros: $count\n\n";
    }
    
    echo "=== DATOS DE EJEMPLO ===\n\n";
    
    // Mostrar algunos usuarios
    echo "👥 USUARIOS:\n";
    $userStmt = $pdo->query("SELECT id, nombres, apellido_paterno, email, rol FROM usuarios LIMIT 5");
    $users = $userStmt->fetchAll();
    foreach ($users as $user) {
        echo "  • ID:{$user['id']} - {$user['nombres']} {$user['apellido_paterno']} ({$user['email']}) - Rol: {$user['rol']}\n";
    }
    
    echo "\n💰 PUNTOS:\n";
    $pointsStmt = $pdo->query("SELECT p.usuario_id, u.nombres, u.apellido_paterno, p.saldo 
                              FROM puntos p 
                              JOIN usuarios u ON p.usuario_id = u.id 
                              LIMIT 5");
    $points = $pointsStmt->fetchAll();
    foreach ($points as $point) {
        echo "  • {$point['nombres']} {$point['apellido_paterno']}: {$point['saldo']} puntos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>