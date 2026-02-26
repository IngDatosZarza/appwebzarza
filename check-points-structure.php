<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== ESTRUCTURA DE TABLAS RELEVANTES ===\n\n";
    
    // Tabla puntos
    echo "📋 Tabla: puntos\n";
    $stmt = $pdo->query('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'puntos\' AND table_schema = \'appweb\' ORDER BY ordinal_position');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['column_name'] . ": " . $row['data_type'] . "\n";
    }
    
    echo "\n📋 Tabla: transacciones_puntos\n";
    $stmt = $pdo->query('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'transacciones_puntos\' AND table_schema = \'appweb\' ORDER BY ordinal_position');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['column_name'] . ": " . $row['data_type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}