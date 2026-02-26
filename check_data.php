<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== USUARIOS DEL SISTEMA ===\n";
    $stmt = $pdo->query('SELECT nombres, apellido_paterno, email, rol FROM usuarios ORDER BY rol, nombres LIMIT 10');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "👤 " . $row['nombres'] . " " . $row['apellido_paterno'] . " - " . $row['email'] . " (" . $row['rol'] . ")\n";
    }
    
    echo "\n=== TRANSACCIONES DE PUNTOS ===\n";
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM transacciones_puntos');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de transacciones: " . $result['total'] . "\n";
    
    if ($result['total'] > 0) {
        $stmt = $pdo->query('SELECT tipo, COUNT(*) as cantidad FROM transacciones_puntos GROUP BY tipo');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $row['tipo'] . ": " . $row['cantidad'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>