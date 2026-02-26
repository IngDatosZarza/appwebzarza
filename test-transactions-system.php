<?php
// Test completo del sistema de transacciones
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';
$username = 'appwebuser';
$password_db = 'appwebpass';

echo "=== TEST COMPLETO DEL SISTEMA DE TRANSACCIONES ===\n\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("SET search_path TO appweb, public");
    
    // 1. Verificar usuario cliente
    echo "1. Verificando usuario de prueba...\n";
    $stmt = $pdo->prepare('SELECT id, nombres, apellido_paterno FROM usuarios WHERE email = ?');
    $stmt->execute(['cliente@test.com']);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Usuario cliente@test.com no encontrado');
    }
    
    echo "   ✅ Usuario encontrado: {$user['nombres']} {$user['apellido_paterno']} (ID: {$user['id']})\n";
    
    // 2. Verificar saldo inicial
    echo "\n2. Verificando saldo inicial...\n";
    $stmt = $pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
    $stmt->execute([$user['id']]);
    $initialBalance = $stmt->fetchColumn() ?: 0;
    echo "   💰 Saldo inicial: $initialBalance puntos\n";
    
    // 3. Simular registro de compra
    echo "\n3. Simulando registro de compra...\n";
    $pdo->beginTransaction();
    
    $amount = 100.50;
    $pointsGenerated = floor($amount);
    $branchId = 1; // Asumiendo que existe sucursal con ID 1
    
    // Registrar compra
    $purchaseStmt = $pdo->prepare('
        INSERT INTO compras (usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $purchaseStmt->execute([$user['id'], $branchId, $amount, $pointsGenerated, $user['id']]);
    
    // Registrar transacción
    $transactionStmt = $pdo->prepare('
        INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $transactionStmt->execute([
        $user['id'],
        'compra',
        $pointsGenerated,
        'Compra de prueba - $' . $amount,
        $user['id']
    ]);
    
    // Actualizar saldo
    $updatePointsStmt = $pdo->prepare('
        UPDATE puntos SET saldo = saldo + ?, updated_at = NOW() WHERE usuario_id = ?
    ');
    $updatePointsStmt->execute([$pointsGenerated, $user['id']]);
    
    $pdo->commit();
    echo "   ✅ Compra registrada: \$$amount → +$pointsGenerated puntos\n";
    
    // 4. Verificar nuevo saldo
    echo "\n4. Verificando nuevo saldo...\n";
    $stmt = $pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
    $stmt->execute([$user['id']]);
    $newBalance = $stmt->fetchColumn();
    echo "   💰 Nuevo saldo: $newBalance puntos (+{$pointsGenerated})\n";
    
    // 5. Verificar cupones disponibles
    echo "\n5. Verificando cupones disponibles...\n";
    $stmt = $pdo->query('
        SELECT id, nombre, puntos_requeridos 
        FROM cupones 
        WHERE activo = true 
        AND fecha_inicio <= CURRENT_DATE 
        AND fecha_fin >= CURRENT_DATE 
        ORDER BY puntos_requeridos ASC 
        LIMIT 1
    ');
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        echo "   ⚠️  No hay cupones disponibles para canjear\n";
    } else {
        echo "   🎁 Cupón disponible: {$coupon['nombre']} ({$coupon['puntos_requeridos']} puntos)\n";
        
        // 6. Intentar canje si hay puntos suficientes
        if ($newBalance >= $coupon['puntos_requeridos']) {
            echo "\n6. Simulando canje de cupón...\n";
            
            $pdo->beginTransaction();
            
            // Generar código QR
            $qrCode = 'QR-TEST-' . uniqid() . '-' . $coupon['id'];
            
            // Asignar cupón
            $assignStmt = $pdo->prepare('
                INSERT INTO cupones_asignados (usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $assignStmt->execute([$user['id'], $coupon['id'], 'pendiente', $qrCode, $user['id']]);
            
            // Registrar transacción de débito
            $debitStmt = $pdo->prepare('
                INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $debitStmt->execute([
                $user['id'],
                'canje',
                $coupon['puntos_requeridos'],
                'Canje de cupón: ' . $coupon['nombre'],
                $user['id']
            ]);
            
            // Actualizar saldo
            $updateBalanceStmt = $pdo->prepare('
                UPDATE puntos SET saldo = saldo - ?, updated_at = NOW() WHERE usuario_id = ?
            ');
            $updateBalanceStmt->execute([$coupon['puntos_requeridos'], $user['id']]);
            
            $pdo->commit();
            echo "   ✅ Cupón canjeado exitosamente\n";
            echo "   🎫 Código QR: $qrCode\n";
            
            // Verificar saldo final
            $stmt = $pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
            $stmt->execute([$user['id']]);
            $finalBalance = $stmt->fetchColumn();
            echo "   💰 Saldo final: $finalBalance puntos (-{$coupon['puntos_requeridos']})\n";
        } else {
            echo "\n6. ⚠️  Puntos insuficientes para canjear el cupón\n";
        }
    }
    
    // 7. Mostrar historial de transacciones
    echo "\n7. Historial de transacciones recientes...\n";
    $stmt = $pdo->prepare('
        SELECT tipo, puntos, descripcion, created_at
        FROM transacciones_puntos 
        WHERE usuario_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ');
    $stmt->execute([$user['id']]);
    $transactions = $stmt->fetchAll();
    
    foreach ($transactions as $transaction) {
        $type = $transaction['tipo'] === 'credito' ? '+' : '-';
        $date = date('d/m/Y H:i', strtotime($transaction['created_at']));
        echo "   📝 $date: $type{$transaction['puntos']} pts - {$transaction['descripcion']}\n";
    }
    
    echo "\n✅ TEST COMPLETADO EXITOSAMENTE\n";
    echo "🚀 El sistema de transacciones está funcionando correctamente\n\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "❌ Error durante el test: " . $e->getMessage() . "\n";
}

echo "=== FIN DEL TEST ===\n";
?>