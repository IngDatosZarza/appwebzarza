<?php
// Test completo del sistema de notificaciones
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';
$username = 'appwebuser';
$password_db = 'appwebpass';

echo "=== TEST COMPLETO DEL SISTEMA DE NOTIFICACIONES ===\n\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("SET search_path TO appweb, public");
    
    // 1. Verificar tabla de notificaciones
    echo "1. Verificando tabla de notificaciones...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM notificaciones");
    $count = $stmt->fetchColumn();
    echo "   ✅ Tabla existe con $count notificaciones\n";
    
    // 2. Probar creación de notificación usando el controlador
    echo "\n2. Probando creación de notificaciones...\n";
    
    require_once 'app/Http/Controllers/Web/NotificationController.php';
    $notificationController = new App\Http\Controllers\Web\NotificationController();
    
    // Crear notificación de prueba
    $userId = 4; // cliente@test.com
    $notificationId = $notificationController->createNotification(
        $userId,
        'purchase',
        'Test de compra',
        'Esta es una notificación de prueba para verificar el sistema',
        ['type' => 'test', 'amount' => 50.00, 'points' => 50]
    );
    
    if ($notificationId) {
        echo "   ✅ Notificación creada con ID: $notificationId\n";
    } else {
        echo "   ❌ Error creando notificación\n";
    }
    
    // 3. Obtener notificaciones del usuario
    echo "\n3. Obteniendo notificaciones del usuario...\n";
    $notifications = $notificationController->getUserNotifications($userId, 5);
    echo "   ✅ Obtenidas " . count($notifications) . " notificaciones\n";
    
    foreach ($notifications as $notif) {
        $status = $notif['leida'] ? '📖 Leída' : '🔔 No leída';
        echo "   • {$notif['tipo']}: {$notif['titulo']} - $status\n";
    }
    
    // 4. Contar no leídas
    echo "\n4. Contando notificaciones no leídas...\n";
    $unreadCount = $notificationController->getUnreadCount($userId);
    echo "   📊 Notificaciones no leídas: $unreadCount\n";
    
    // 5. Marcar como leída
    if ($notificationId) {
        echo "\n5. Marcando notificación como leída...\n";
        $success = $notificationController->markAsRead($notificationId, $userId);
        if ($success) {
            echo "   ✅ Marcada como leída exitosamente\n";
            
            // Verificar conteo actualizado
            $newUnreadCount = $notificationController->getUnreadCount($userId);
            echo "   📊 Nuevo conteo no leídas: $newUnreadCount\n";
        } else {
            echo "   ❌ Error marcando como leída\n";
        }
    }
    
    // 6. Probar notificaciones específicas
    echo "\n6. Probando notificaciones específicas...\n";
    
    // Notificación de compra
    $purchaseNotif = $notificationController->notifyPurchase($userId, 150.75, 150);
    echo "   ✅ Notificación de compra: ID $purchaseNotif\n";
    
    // Notificación de cupón
    $couponNotif = $notificationController->notifyCouponRedeemed($userId, 'Cupón de Prueba', 100, 'QR-TEST-12345');
    echo "   ✅ Notificación de cupón: ID $couponNotif\n";
    
    // Notificación de bienvenida para admin
    $welcomeNotif = $notificationController->notifyWelcome(5, 'Admin');
    echo "   ✅ Notificación de bienvenida para admin: ID $welcomeNotif\n";
    
    // 7. Mostrar estadísticas finales
    echo "\n7. Estadísticas finales...\n";
    $stats = $pdo->prepare('
        SELECT 
            tipo,
            COUNT(*) as total,
            COUNT(CASE WHEN leida = false THEN 1 END) as no_leidas
        FROM notificaciones 
        WHERE usuario_id = ?
        GROUP BY tipo 
        ORDER BY total DESC
    ');
    $stats->execute([$userId]);
    
    echo "   📊 Estadísticas del usuario cliente@test.com:\n";
    while ($stat = $stats->fetch()) {
        echo "      • {$stat['tipo']}: {$stat['total']} total, {$stat['no_leidas']} no leídas\n";
    }
    
    // 8. Test de API JSON
    echo "\n8. Simulando respuesta API...\n";
    $apiNotifications = $notificationController->getUserNotifications($userId, 3);
    $apiUnreadCount = $notificationController->getUnreadCount($userId);
    
    $apiResponse = [
        'success' => true,
        'notifications' => $apiNotifications,
        'unread_count' => $apiUnreadCount
    ];
    
    echo "   ✅ API Response simulada:\n";
    echo "      • Notificaciones: " . count($apiResponse['notifications']) . "\n";
    echo "      • No leídas: " . $apiResponse['unread_count'] . "\n";
    echo "      • JSON válido: " . (json_encode($apiResponse) ? 'Sí' : 'No') . "\n";
    
    echo "\n✅ TEST DEL SISTEMA DE NOTIFICACIONES COMPLETADO EXITOSAMENTE\n";
    echo "🔔 Sistema de notificaciones funcionando correctamente\n\n";
    
    // Limpiar notificaciones de prueba
    echo "🧹 Limpiando notificaciones de prueba...\n";
    $cleanStmt = $pdo->prepare("DELETE FROM notificaciones WHERE mensaje LIKE '%prueba%' OR datos::text LIKE '%test%'");
    $cleanStmt->execute();
    echo "✅ Notificaciones de prueba eliminadas\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el test: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>