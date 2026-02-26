<?php
// Crear cupón de prueba con pocos puntos
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
    
    // Crear cupón con pocos puntos
    $stmt = $pdo->prepare('
        INSERT INTO cupones (nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, created_at, updated_at)
        VALUES (?, ?, ?, CURRENT_DATE, CURRENT_DATE + INTERVAL \'30 days\', true, NOW(), NOW())
    ');
    
    $stmt->execute([
        'Cupón de Prueba - 50 pts',
        'Cupón especial para testing con solo 50 puntos requeridos',
        50
    ]);
    
    echo "✅ Cupón de prueba creado exitosamente (50 puntos)\n";
    
    // Mostrar cupones disponibles
    echo "\nCupones disponibles:\n";
    $stmt = $pdo->query('
        SELECT nombre, puntos_requeridos, fecha_fin 
        FROM cupones 
        WHERE activo = true 
        ORDER BY puntos_requeridos ASC
    ');
    
    while ($coupon = $stmt->fetch()) {
        echo "  • {$coupon['nombre']}: {$coupon['puntos_requeridos']} puntos (válido hasta {$coupon['fecha_fin']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>