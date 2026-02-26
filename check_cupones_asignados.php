<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== ESTRUCTURA COMPLETA CUPONES_ASIGNADOS ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'cupones_asignados' AND table_schema = 'appweb' 
        ORDER BY ordinal_position
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . ' (' . $row['data_type'] . ') - Nullable: ' . $row['is_nullable'] . ' - Default: ' . ($row['column_default'] ?? 'NULL') . "\n";
    }
    
    echo "\n=== VERIFICAR SI EXISTE COLUMNA 'estado' ===\n";
    $stmt = $pdo->query("
        SELECT COUNT(*) as existe 
        FROM information_schema.columns 
        WHERE table_name = 'cupones_asignados' 
        AND column_name = 'estado' 
        AND table_schema = 'appweb'
    ");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Columna 'estado' existe: " . ($resultado['existe'] > 0 ? "SÍ" : "NO") . "\n";
    
    echo "\n=== VERIFICAR RESTRICCIONES EN ESTADO ===\n";
    $stmt = $pdo->query("
        SELECT conname, pg_get_constraintdef(oid) as definition
        FROM pg_constraint 
        WHERE conrelid = (SELECT oid FROM pg_class WHERE relname = 'cupones_asignados')
        AND contype = 'c'
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['conname'] . ': ' . $row['definition'] . "\n";
    }
    
    echo "\n=== CUPONES ASIGNADOS ACTUALES ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones_asignados");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total cupones asignados: " . $total['total'] . "\n";
    
    if ($total['total'] > 0) {
        echo "\n=== ESTADOS ACTUALES ===\n";
        $stmt = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM cupones_asignados GROUP BY estado");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['estado'] . ': ' . $row['cantidad'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>