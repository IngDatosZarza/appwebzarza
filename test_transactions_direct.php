<?php
// Test simple del controlador showTransactions - solo PDO

try {
    // Probar la conexión PDO directamente
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "✅ Conexión PDO exitosa\n";
    
    // Probar consulta básica
    $stmt = $pdo->query('SELECT COUNT(*) FROM transacciones_puntos');
    $count = $stmt->fetchColumn();
    echo "✅ Total transacciones: $count\n";
    
    // Probar consulta compleja similar a la del controlador
    $sql = "
        SELECT 
            tp.id,
            tp.tipo,
            tp.puntos,
            tp.descripcion,
            tp.created_at,
            u.nombres || ' ' || u.apellido_paterno as usuario_nombre,
            u.email as usuario_email,
            CASE 
                WHEN tp.tipo = 'compra' THEN 'positivo'
                WHEN tp.tipo = 'canje' THEN 'negativo'
                ELSE 'neutro'
            END as tipo_movimiento,
            CASE 
                WHEN tp.tipo = 'compra' THEN '🛒 Compra'
                WHEN tp.tipo = 'canje' THEN '🎫 Canje'
                WHEN tp.tipo = 'ajuste' THEN '⚖️ Ajuste'
                ELSE tp.tipo
            END as tipo_descripcion
        FROM transacciones_puntos tp
        LEFT JOIN usuarios u ON tp.usuario_id = u.id
        ORDER BY tp.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Consulta compleja exitosa. Resultados: " . count($result) . "\n";
    
    foreach ($result as $row) {
        echo "- ID: {$row['id']}, Usuario: {$row['usuario_nombre']}, Tipo: {$row['tipo']}, Puntos: {$row['puntos']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
?>