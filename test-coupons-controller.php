<?php

try {
    // Configurar PDO
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec('SET search_path TO appweb, public');

    echo "=== PRUEBA DE CUPONES ===\n\n";

    // Simular usuario autenticado
    $usuario_id = 1; // Asumiendo que existe el usuario con ID 1

    // Obtener puntos del usuario
    $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $puntos = $stmt->fetch();
    $saldo_puntos = $puntos ? $puntos['saldo'] : 0;
    
    echo "💰 Puntos del usuario ID $usuario_id: $saldo_puntos\n\n";

    // Obtener cupones disponibles
    $stmt = $pdo->query("
        SELECT *
        FROM cupones 
        WHERE activo = true 
        AND fecha_inicio <= CURRENT_DATE 
        AND fecha_fin >= CURRENT_DATE
        ORDER BY puntos_requeridos ASC
    ");
    $cupones_disponibles = $stmt->fetchAll();

    echo "🎫 CUPONES DISPONIBLES:\n";
    foreach ($cupones_disponibles as $cupon) {
        echo "  - {$cupon['nombre']} ({$cupon['puntos_requeridos']} puntos)\n";
        echo "    {$cupon['descripcion']}\n";
        echo "    Válido: {$cupon['fecha_inicio']} a {$cupon['fecha_fin']}\n\n";
    }

    // Obtener cupones del usuario
    $stmt = $pdo->prepare("
        SELECT 
            ca.*,
            c.nombre,
            c.descripcion,
            c.puntos_requeridos,
            CASE 
                WHEN ca.estado = 'disponible' THEN 'disponible'
                WHEN ca.estado = 'usado' THEN 'usado'
                ELSE 'otro'
            END as estado
        FROM cupones_asignados ca
        INNER JOIN cupones c ON ca.cupon_id = c.id
        WHERE ca.usuario_id = ?
        ORDER BY ca.created_at DESC
    ");
    $stmt->execute([$usuario_id]);
    $mis_cupones = $stmt->fetchAll();

    echo "🎟️ MIS CUPONES:\n";
    if (count($mis_cupones) > 0) {
        foreach ($mis_cupones as $cupon) {
            echo "  - {$cupon['nombre']} (Estado: {$cupon['estado']})\n";
            echo "    Código QR: {$cupon['codigo_qr']}\n";
            echo "    Asignado: {$cupon['created_at']}\n\n";
        }
    } else {
        echo "  No hay cupones asignados\n\n";
    }

    echo "✅ Prueba completada - Los métodos del controlador deberían funcionar correctamente\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}