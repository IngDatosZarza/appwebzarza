<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    echo "✅ Conexión BD exitosa\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
    echo "👥 Usuarios: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM compras');
    echo "🛒 Compras: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM cupones');
    echo "🎫 Cupones: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM sucursales');
    echo "🏪 Sucursales: " . $stmt->fetchColumn() . "\n";
    
    echo "\n=== USUARIOS Y SUS PUNTOS ===\n";
    $stmt = $pdo->query("SELECT u.nombres || ' ' || u.apellido_paterno as nombre, u.email, u.rol, COALESCE(p.saldo, 0) as puntos FROM usuarios u LEFT JOIN puntos p ON u.id = p.usuario_id ORDER BY u.rol, u.nombres");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("👤 %s (%s) - %s: %d puntos\n", $row['nombre'], $row['rol'], $row['email'], $row['puntos']);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>