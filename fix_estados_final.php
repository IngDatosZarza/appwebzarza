<?php
/**
 * Actualiza estados sin constraint, luego agrega constraint correcta
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "Actualizando estados de cupones_asignados...\n\n";
    
    // 1. Eliminar constraint
    echo "1. Eliminando constraint antigua...\n";
    $pdo->exec("ALTER TABLE cupones_asignados DROP CONSTRAINT IF EXISTS cupones_asignados_estado_check");
    echo "   ✅ Constraint eliminada\n\n";
    
    // 2. Mostrar estados actuales
    echo "2. Estados ACTUALES:\n";
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estados as $estado) {
        echo "   - {$estado['estado']}: {$estado['total']}\n";
    }
    echo "\n";
    
    // 3. Actualizar estados sin constraint
    echo "3. Actualizando estados...\n";
    
    $stmt = $pdo->prepare("UPDATE cupones_asignados SET estado = ? WHERE estado = ?");
    $stmt->execute(['asignado', 'pendiente']);
    $updated_pendiente = $stmt->rowCount();
    echo "   ✅ Actualizados $updated_pendiente cupones: 'pendiente' → 'asignado'\n";
    
    $stmt = $pdo->prepare("UPDATE cupones_asignados SET estado = ? WHERE estado = ?");
    $stmt->execute(['usado', 'redimido']);
    $updated_redimido = $stmt->rowCount();
    echo "   ✅ Actualizados $updated_redimido cupones: 'redimido' → 'usado'\n\n";
    
    // 4. Mostrar estados después de actualizar
    echo "4. Estados DESPUÉS de actualización:\n";
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM cupones_asignados GROUP BY estado");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estados as $estado) {
        echo "   - {$estado['estado']}: {$estado['total']}\n";
    }
    echo "\n";
    
    // 5. Agregar nueva constraint
    echo "5. Agregando constraint nueva...\n";
    $pdo->exec("
        ALTER TABLE cupones_asignados 
        ADD CONSTRAINT cupones_asignados_estado_check 
        CHECK (estado IN ('asignado', 'usado', 'vencido', 'bloqueado'))
    ");
    echo "   ✅ Constraint agregada con estados: asignado, usado, vencido, bloqueado\n\n";
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE\n";
    echo str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
