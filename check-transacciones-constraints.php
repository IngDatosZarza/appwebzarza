<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    echo "Verificando constraints de transacciones_puntos:\n";
    $stmt = $pdo->query("SELECT constraint_name, check_clause FROM information_schema.check_constraints WHERE constraint_schema = 'appweb' AND table_name = 'transacciones_puntos'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['constraint_name'] . ': ' . $row['check_clause'] . "\n";
    }
    
    echo "\nVerificando registros existentes:\n";
    $stmt = $pdo->query("SELECT DISTINCT tipo FROM transacciones_puntos");
    $tipos = $stmt->fetchAll();
    foreach ($tipos as $tipo) {
        echo "  - " . $tipo['tipo'] . "\n";
    }
    
} catch (Exception $e) { 
    echo $e->getMessage(); 
}