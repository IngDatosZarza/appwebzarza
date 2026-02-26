<?php
// Verificar qué tipos son válidos
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
    
    // Probar valores que podrían estar permitidos
    $test_types = ['compra', 'canje', 'recompensa', 'bonus', 'ajuste', 'premio', 'descuento'];
    
    echo "=== PROBANDO TIPOS VÁLIDOS ===\n\n";
    
    foreach ($test_types as $type) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, created_at) VALUES (4, ?, 5, 'Test $type', NOW())");
            $stmt->execute([$type]);
            echo "✅ '$type' es válido\n";
            
            // Mantener el registro para ver qué funciona
            
        } catch (Exception $e) {
            echo "❌ '$type' NO es válido\n";
        }
    }
    
    // Mostrar registros creados
    echo "\nRegistros de prueba creados:\n";
    $stmt = $pdo->query("SELECT * FROM transacciones_puntos WHERE descripcion LIKE 'Test %' ORDER BY id");
    $tests = $stmt->fetchAll();
    
    foreach ($tests as $test) {
        echo "  Tipo: '{$test['tipo']}' - {$test['descripcion']}\n";
    }
    
    // Limpiar registros de prueba
    $pdo->exec("DELETE FROM transacciones_puntos WHERE descripcion LIKE 'Test %'");
    echo "\n✅ Registros de prueba eliminados\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>