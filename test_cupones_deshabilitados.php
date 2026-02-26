<?php
/**
 * Verifica que los cupones ya canjeados se marquen correctamente
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== VERIFICACIÓN DE CUPONES CANJEADOS ===\n\n";
    
    // Obtener un usuario cliente
    $stmt = $pdo->query("SELECT id, nombres, email FROM usuarios WHERE rol = 'cliente' LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        echo "❌ No hay clientes en el sistema\n";
        exit(1);
    }
    
    echo "Cliente de prueba:\n";
    echo "👤 {$cliente['nombres']} ({$cliente['email']})\n";
    echo "ID: {$cliente['id']}\n\n";
    
    // Simular la consulta que hace el controlador
    echo "--- Cupones Disponibles (con estado de canje) ---\n\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.nombre,
            c.codigo,
            c.puntos_requeridos,
            CASE 
                WHEN ca.id IS NOT NULL THEN true
                ELSE false
            END as ya_canjeado
        FROM cupones c
        LEFT JOIN cupones_asignados ca ON c.id = ca.cupon_id AND ca.usuario_id = ?
        WHERE c.activo = true 
        AND c.fecha_inicio <= CURRENT_DATE 
        AND c.fecha_fin >= CURRENT_DATE
        ORDER BY c.puntos_requeridos ASC
        LIMIT 10
    ");
    
    $stmt->execute([$cliente['id']]);
    $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $canjeados = 0;
    $disponibles = 0;
    
    foreach ($cupones as $cupon) {
        $estado_icon = $cupon['ya_canjeado'] ? '🟠' : '🟢';
        $estado_text = $cupon['ya_canjeado'] ? 'YA CANJEADO' : 'DISPONIBLE';
        
        echo "$estado_icon {$cupon['nombre']} ({$cupon['codigo']})\n";
        echo "   Puntos: {$cupon['puntos_requeridos']}\n";
        echo "   Estado: $estado_text\n";
        
        if ($cupon['ya_canjeado']) {
            echo "   Botón: [✓ Ya Canjeado] (deshabilitado, naranja)\n";
            $canjeados++;
        } else {
            echo "   Botón: [Canjear Cupón] (activo, morado)\n";
            $disponibles++;
        }
        echo "\n";
    }
    
    echo str_repeat("-", 50) . "\n";
    echo "📊 Resumen:\n";
    echo "   Total cupones: " . count($cupones) . "\n";
    echo "   🟢 Disponibles: $disponibles\n";
    echo "   🟠 Ya canjeados: $canjeados\n\n";
    
    // Mostrar cupones canjeados por el usuario
    echo "--- Cupones Canjeados por {$cliente['nombres']} ---\n\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            c.nombre,
            c.codigo,
            ca.codigo_qr,
            ca.estado,
            ca.created_at
        FROM cupones_asignados ca
        INNER JOIN cupones c ON ca.cupon_id = c.id
        WHERE ca.usuario_id = ?
        ORDER BY ca.created_at DESC
    ");
    
    $stmt->execute([$cliente['id']]);
    $canjeados_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($canjeados_list) > 0) {
        foreach ($canjeados_list as $canje) {
            $estado_icon = [
                'asignado' => '🟢',
                'usado' => '⚫',
                'vencido' => '🔴',
                'bloqueado' => '🔒'
            ][$canje['estado']] ?? '❓';
            
            echo "$estado_icon {$canje['nombre']} ({$canje['codigo']})\n";
            echo "   QR: {$canje['codigo_qr']}\n";
            echo "   Estado: {$canje['estado']}\n";
            echo "   Fecha: {$canje['created_at']}\n\n";
        }
    } else {
        echo "   ℹ️  No ha canjeado ningún cupón aún\n\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ VERIFICACIÓN COMPLETADA\n\n";
    
    echo "🧪 Para probar:\n";
    echo "1. Inicia sesión: {$cliente['email']} / password\n";
    echo "2. Ve a: http://localhost:8000/cupones\n";
    echo "3. Verifica que los cupones ya canjeados tengan botón naranja\n";
    echo "4. Intenta hacer click: no debe pasar nada (deshabilitado)\n";
    echo str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
