<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    $stmt = $pdo->query('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'usuarios\' AND table_schema = \'appweb\' ORDER BY ordinal_position');
    echo "Columnas de la tabla usuarios:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['column_name'] . ": " . $row['data_type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}