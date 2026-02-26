<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== USUARIOS DISPONIBLES ===\n";
    $stmt = $pdo->query("SELECT nombres, apellido_paterno, email, rol FROM usuarios ORDER BY rol, nombres");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "👤 {$row['nombres']} {$row['apellido_paterno']} ({$row['rol']}) - {$row['email']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>