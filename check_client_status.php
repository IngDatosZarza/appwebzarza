<?php
/**
 * Verificar estado del cliente y cupones disponibles
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== ESTADO DE CLIENTES ===\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            u.id, 
            u.nombres, 
            u.apellido_paterno, 
            u.email, 
            COALESCE(p.saldo, 0) as puntos
        FROM usuarios u
        LEFT JOIN puntos p ON u.id = p.usuario_id
        WHERE u.rol = 'cliente'
        ORDER BY u.id
        LIMIT 5
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "👤 {$row['nombres']} {$row['apellido_paterno']}\n";
        echo "   📧 Email: {$row['email']}\n";
        echo "   💰 Puntos: {$row['puntos']}\n\n";
    }
    
    echo "\n=== CUPONES DISPONIBLES (≤ 100 puntos) ===\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre, 
            codigo, 
            puntos_requeridos,
            fecha_inicio,
            fecha_fin
        FROM cupones
        WHERE activo = true 
        AND puntos_requeridos <= 100
        AND fecha_inicio <= CURRENT_DATE
        AND fecha_fin >= CURRENT_DATE
        ORDER BY puntos_requeridos
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "🎫 {$row['nombre']} ({$row['codigo']})\n";
        echo "   ID: {$row['id']}\n";
        echo "   Puntos: {$row['puntos_requeridos']}\n";
        echo "   Válido: {$row['fecha_inicio']} a {$row['fecha_fin']}\n\n";
    }
    
    echo "\n=== INSTRUCCIONES ===\n\n";
    echo "1. Inicia sesión con: cliente@test.com / password\n";
    echo "2. Ve a: http://localhost:8000/cupones\n";
    echo "3. Click en 'Canjear Cupón' en cualquier cupón disponible\n";
    echo "4. Confirma el canje\n";
    echo "5. ¡Verás el popup con tu cupón y código QR!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
