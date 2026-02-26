<?php
// Crear tabla de notificaciones
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';
$username = 'appwebuser';
$password_db = 'appwebpass';

echo "=== CREANDO TABLA DE NOTIFICACIONES ===\n\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("SET search_path TO appweb, public");
    
    // Verificar si la tabla ya existe
    $checkTable = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'appweb' 
            AND table_name = 'notificaciones'
        )
    ");
    
    if ($checkTable->fetchColumn()) {
        echo "⚠️  La tabla 'notificaciones' ya existe. Eliminándola para recrear...\n";
        $pdo->exec("DROP TABLE IF EXISTS notificaciones CASCADE");
    }
    
    // Crear tabla de notificaciones
    $createTable = "
        CREATE TABLE notificaciones (
            id BIGSERIAL PRIMARY KEY,
            usuario_id BIGINT NOT NULL,
            tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('welcome', 'purchase', 'coupon', 'expiry', 'promotion', 'system')),
            titulo VARCHAR(255) NOT NULL,
            mensaje TEXT NOT NULL,
            datos JSONB,
            leida BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )
    ";
    
    $pdo->exec($createTable);
    echo "✅ Tabla 'notificaciones' creada exitosamente\n";
    
    // Crear índices para optimizar consultas
    $pdo->exec("CREATE INDEX idx_notificaciones_usuario_id ON notificaciones(usuario_id)");
    $pdo->exec("CREATE INDEX idx_notificaciones_leida ON notificaciones(leida)");
    $pdo->exec("CREATE INDEX idx_notificaciones_created_at ON notificaciones(created_at DESC)");
    
    echo "✅ Índices creados para optimización\n";
    
    // Insertar algunas notificaciones de prueba
    echo "\n📝 Insertando notificaciones de prueba...\n";
    
    // Para el usuario cliente@test.com (ID: 4)
    $notificaciones = [
        [
            'usuario_id' => 4,
            'tipo' => 'welcome',
            'titulo' => '¡Bienvenido a FidelityPoints!',
            'mensaje' => 'Gracias por unirte a nuestro programa de puntos. ¡Empieza a ganar puntos con cada compra!',
            'datos' => json_encode(['type' => 'welcome'])
        ],
        [
            'usuario_id' => 4,
            'tipo' => 'purchase',
            'titulo' => '¡Puntos ganados!',
            'mensaje' => 'Has ganado 100 puntos por tu compra de $100.50',
            'datos' => json_encode(['type' => 'purchase', 'amount' => 100.50, 'points' => 100])
        ],
        [
            'usuario_id' => 4,
            'tipo' => 'coupon',
            'titulo' => 'Cupón canjeado',
            'mensaje' => 'Has canjeado exitosamente el cupón "Cupón de Prueba - 50 pts" por 50 puntos',
            'datos' => json_encode(['type' => 'coupon_redeemed', 'coupon_name' => 'Cupón de Prueba - 50 pts', 'points_used' => 50])
        ],
        [
            'usuario_id' => 4,
            'tipo' => 'promotion',
            'titulo' => '¡Oferta especial!',
            'mensaje' => 'Este fin de semana gana puntos dobles en todas tus compras. ¡No te lo pierdas!',
            'datos' => json_encode(['type' => 'promotion', 'multiplier' => 2])
        ]
    ];
    
    foreach ($notificaciones as $notif) {
        $stmt = $pdo->prepare('
            INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, datos, leida, created_at)
            VALUES (?, ?, ?, ?, ?, false, NOW() - INTERVAL \'' . rand(1, 48) . ' hours\')
        ');
        
        $stmt->execute([
            $notif['usuario_id'],
            $notif['tipo'],
            $notif['titulo'],
            $notif['mensaje'],
            $notif['datos']
        ]);
    }
    
    echo "✅ " . count($notificaciones) . " notificaciones de prueba insertadas\n";
    
    // Para el usuario admin también
    $adminNotif = [
        'usuario_id' => 5, // admin@test.com
        'tipo' => 'system',
        'titulo' => 'Sistema de notificaciones activado',
        'mensaje' => 'El sistema de notificaciones ha sido activado exitosamente para todos los usuarios.',
        'datos' => json_encode(['type' => 'system', 'feature' => 'notifications'])
    ];
    
    $stmt = $pdo->prepare('
        INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, datos, leida, created_at)
        VALUES (?, ?, ?, ?, ?, false, NOW())
    ');
    
    $stmt->execute([
        $adminNotif['usuario_id'],
        $adminNotif['tipo'],
        $adminNotif['titulo'],
        $adminNotif['mensaje'],
        $adminNotif['datos']
    ]);
    
    echo "✅ Notificación de admin creada\n";
    
    // Mostrar estadísticas
    echo "\n📊 Estadísticas de notificaciones:\n";
    $stats = $pdo->query('
        SELECT 
            tipo,
            COUNT(*) as total,
            COUNT(CASE WHEN leida = false THEN 1 END) as no_leidas
        FROM notificaciones 
        GROUP BY tipo 
        ORDER BY total DESC
    ')->fetchAll();
    
    foreach ($stats as $stat) {
        echo "  • {$stat['tipo']}: {$stat['total']} total, {$stat['no_leidas']} no leídas\n";
    }
    
    echo "\n✅ TABLA DE NOTIFICACIONES CREADA EXITOSAMENTE\n";
    echo "🔔 Sistema de notificaciones listo para usar\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>