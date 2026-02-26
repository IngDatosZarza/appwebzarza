<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== ESTRUCTURA DE CUPONES ===\n";
    
    $tables = ['cupones', 'cupones_asignados', 'redenciones'];
    
    foreach($tables as $table) {
        echo "\n📋 Tabla: $table\n";
        $result = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$table' AND table_schema = 'appweb' ORDER BY ordinal_position");
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['column_name']}: {$row['data_type']}\n";
        }
    }
    
    echo "\n=== DATOS ACTUALES ===\n";
    
    // Cupones disponibles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones WHERE activo = true");
    $cupones = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "💫 Cupones activos: {$cupones['total']}\n";
    
    // Cupones asignados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones_asignados");
    $asignados = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "🎫 Cupones asignados: {$asignados['total']}\n";
    
    // Redenciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM redenciones");
    $redenciones = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Redenciones realizadas: {$redenciones['total']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>