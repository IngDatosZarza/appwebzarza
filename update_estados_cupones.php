<?php
/**
 * Actualiza estados de cupones_asignados de 'pendiente' a 'asignado'
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "Actualizando estados de cupones asignados...\n\n";
    
    // Mostrar estados actuales
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados_antes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estados ANTES de la actualización:\n";
    foreach ($estados_antes as $estado) {
        echo "  - {$estado['estado']}: {$estado['total']}\n";
    }
    
    // Actualizar de 'pendiente' a 'asignado'
    $stmt = $pdo->prepare("UPDATE cupones_asignados SET estado = ? WHERE estado = ?");
    $stmt->execute(['asignado', 'pendiente']);
    $updated = $stmt->rowCount();
    
    echo "\n✅ Actualizados $updated cupones de 'pendiente' a 'asignado'\n\n";
    
    // Mostrar estados después
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados_despues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estados DESPUÉS de la actualización:\n";
    foreach ($estados_despues as $estado) {
        echo "  - {$estado['estado']}: {$estado['total']}\n";
    }
    
    echo "\n✅ Actualización completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
