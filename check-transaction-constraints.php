<?php
// Verificar restricciones en la tabla transacciones_puntos
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
    
    echo "=== VERIFICANDO RESTRICCIONES EN TRANSACCIONES_PUNTOS ===\n\n";
    
    // Verificar datos existentes
    echo "Datos existentes en transacciones_puntos:\n";
    $stmt = $pdo->query('SELECT * FROM transacciones_puntos ORDER BY id');
    $existing = $stmt->fetchAll();
    
    if (empty($existing)) {
        echo "  No hay datos existentes\n";
    } else {
        foreach ($existing as $row) {
            echo "  ID: {$row['id']} | Tipo: '{$row['tipo']}' | Puntos: {$row['puntos']} | Desc: {$row['descripcion']}\n";
        }
    }
    
    echo "\n";
    
    // Probar valores válidos para tipo
    $test_types = ['credito', 'debito', 'crédito', 'débito', 'credit', 'debit', 'ganado', 'gastado'];
    
    foreach ($test_types as $type) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, created_at) VALUES (4, ?, 10, 'Test tipo', NOW())");
            $stmt->execute([$type]);
            echo "✅ '$type' es válido\n";
            
            // Eliminar el registro de prueba
            $pdo->exec("DELETE FROM transacciones_puntos WHERE descripcion = 'Test tipo'");
            
        } catch (Exception $e) {
            echo "❌ '$type' NO es válido: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>