<?php
/**
 * Script para agregar la columna QR_CODE faltante
 */

echo "🔧 AGREGANDO COLUMNA QR_CODE\n";
echo "============================\n\n";

try {
    // Conectar a la base de datos
    echo "1. Conectando a la base de datos...\n";
    
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "   ✅ Conexión exitosa\n\n";
    
    // Verificar si la columna ya existe
    echo "2. Verificando estructura de la tabla cupones_asignados...\n";
    
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_schema = 'appweb' 
        AND table_name = 'cupones_asignados'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Columnas actuales: " . implode(', ', $columns) . "\n\n";
    
    // Agregar columna QR_CODE si no existe
    if (!in_array('qr_code', $columns)) {
        echo "3. Agregando columna qr_code...\n";
        
        $pdo->exec("
            ALTER TABLE cupones_asignados 
            ADD COLUMN qr_code VARCHAR(255) NULL
        ");
        
        echo "   ✅ Columna qr_code agregada\n\n";
    } else {
        echo "3. La columna qr_code ya existe\n\n";
    }
    
    // Generar códigos QR para cupones existentes sin QR
    echo "4. Generando códigos QR para cupones existentes...\n";
    
    $stmt = $pdo->query("
        SELECT id, usuario_id, cupon_id 
        FROM cupones_asignados 
        WHERE qr_code IS NULL
    ");
    
    $updateCount = 0;
    $updateStmt = $pdo->prepare("
        UPDATE cupones_asignados 
        SET qr_code = ? 
        WHERE id = ?
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $qrCode = 'QR_' . $row['id'] . '_' . date('YmdHis') . '_' . substr(md5($row['usuario_id'] . $row['cupon_id']), 0, 8);
        $updateStmt->execute([$qrCode, $row['id']]);
        $updateCount++;
    }
    
    echo "   ✅ $updateCount códigos QR generados\n\n";
    
    // Verificar resultado final
    echo "5. Verificación final...\n";
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(qr_code) as con_qr,
            COUNT(*) - COUNT(qr_code) as sin_qr
        FROM cupones_asignados
    ");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   📊 Total cupones: {$result['total']}\n";
    echo "   ✅ Con QR: {$result['con_qr']}\n";
    echo "   ❌ Sin QR: {$result['sin_qr']}\n\n";
    
    // Mostrar algunos ejemplos
    echo "6. Ejemplos de códigos QR generados...\n";
    
    $stmt = $pdo->query("
        SELECT 
            u.nombres || ' ' || u.apellido_paterno as usuario,
            c.nombre as cupon,
            ca.qr_code,
            ca.usado
        FROM cupones_asignados ca
        JOIN usuarios u ON ca.usuario_id = u.id
        JOIN cupones c ON ca.cupon_id = c.id
        LIMIT 5
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $estado = $row['usado'] ? '🔴 Usado' : '🟢 Activo';
        echo "   🎫 {$row['usuario']} - {$row['cupon']} ({$estado})\n";
        echo "      QR: {$row['qr_code']}\n\n";
    }
    
    echo "🎉 ¡COLUMNA QR_CODE AGREGADA EXITOSAMENTE!\n";
    echo "\n✨ Ahora el sistema de QR debería funcionar completamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}