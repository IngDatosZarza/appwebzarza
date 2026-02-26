<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== PRIMEROS 20 REGISTROS DE CÓDIGOS POSTALES ===\n\n";
    
    $stmt = $pdo->query('SELECT * FROM codigos_postales LIMIT 20');
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf(
            "CP: %-6s | Estado: %-30s | Municipio: %-30s | Colonia: %s\n",
            $row['codigo_postal'],
            substr($row['estado'], 0, 30),
            substr($row['municipio'], 0, 30),
            substr($row['colonia'], 0, 50)
        );
    }
    
    echo "\n=== ESTADÍSTICAS ===\n\n";
    
    $total = $pdo->query('SELECT COUNT(*) FROM codigos_postales')->fetchColumn();
    echo "Total registros: " . number_format($total) . "\n";
    
    $estados = $pdo->query('SELECT COUNT(DISTINCT estado) FROM codigos_postales')->fetchColumn();
    echo "Estados únicos: " . number_format($estados) . "\n";
    
    $municipios = $pdo->query('SELECT COUNT(DISTINCT municipio) FROM codigos_postales')->fetchColumn();
    echo "Municipios únicos: " . number_format($municipios) . "\n";
    
    echo "\n=== ESTADOS MÁS COMUNES ===\n\n";
    $stmt = $pdo->query('SELECT estado, COUNT(*) as total FROM codigos_postales GROUP BY estado ORDER BY total DESC LIMIT 10');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['estado'] . ": " . number_format($row['total']) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
