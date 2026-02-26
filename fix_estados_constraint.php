<?php
/**
 * Actualiza la constraint del enum estado en cupones_asignados
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "Actualizando constraint de estados...\n\n";
    
    // Primero eliminar la constraint antigua
    echo "1. Eliminando constraint antigua...\n";
    $pdo->exec("ALTER TABLE cupones_asignados DROP CONSTRAINT IF EXISTS cupones_asignados_estado_check");
    echo "   ✅ Constraint antigua eliminada\n\n";
    
    // Agregar la nueva constraint con los estados correctos
    echo "2. Agregando constraint nueva...\n";
    $pdo->exec("
        ALTER TABLE cupones_asignados 
        ADD CONSTRAINT cupones_asignados_estado_check 
        CHECK (estado IN ('asignado', 'usado', 'vencido', 'bloqueado'))
    ");
    echo "   ✅ Constraint nueva agregada\n\n";
    
    // Ahora actualizar los estados
    echo "3. Actualizando estados existentes...\n";
    
    // Mostrar estados actuales
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados_antes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Estados ANTES:\n";
    foreach ($estados_antes as $estado) {
        echo "     - {$estado['estado']}: {$estado['total']}\n";
    }
    
    // Actualizar de 'pendiente' a 'asignado'
    $stmt = $pdo->prepare("UPDATE cupones_asignados SET estado = ? WHERE estado = ?");
    $stmt->execute(['asignado', 'pendiente']);
    $updated_pendiente = $stmt->rowCount();
    
    // Actualizar de 'redimido' a 'usado'
    $stmt = $pdo->prepare("UPDATE cupones_asignados SET estado = ? WHERE estado = ?");
    $stmt->execute(['usado', 'redimido']);
    $updated_redimido = $stmt->rowCount();
    
    echo "\n   ✅ Actualizados $updated_pendiente cupones de 'pendiente' a 'asignado'\n";
    echo "   ✅ Actualizados $updated_redimido cupones de 'redimido' a 'usado'\n\n";
    
    // Mostrar estados después
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados_despues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Estados DESPUÉS:\n";
    foreach ($estados_despues as $estado) {
        echo "     - {$estado['estado']}: {$estado['total']}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE\n";
    echo str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
