<?php

namespace App\Http\Controllers\Web;

use Exception;
use PDO;
use PDOException;

class NotificationController
{
    private $pdo;
    
    public function __construct()
    {
        try {
            $this->pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $this->pdo->exec('SET search_path TO appweb, public');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Alias para compatibilidad con rutas Laravel
     */
    public function index()
    {
        return $this->showNotifications();
    }

    public function getNotifications()
    {
        return $this->getNotificationsApi();
    }

    public function markAsRead()
    {
        return $this->markAsReadApi();
    }

    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        
        try {
            $success = $this->markAllAsReadForUser($_SESSION['user_id']);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No se pudieron marcar como leídas']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error del servidor']);
        }
    }
    
    /**
     * Crear una nueva notificación
     */
    public function createNotification($userId, $type, $title, $message, $data = null)
    {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, datos, leida, created_at)
                VALUES (?, ?, ?, ?, ?, false, NOW())
            ');
            
            $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                $data ? json_encode($data) : null
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (\Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notificación por compra realizada
     */
    public function notifyPurchase($userId, $amount, $pointsEarned)
    {
        $title = "¡Puntos ganados!";
        $message = "Has ganado {$pointsEarned} puntos por tu compra de $" . number_format($amount, 2);
        
        $data = [
            'type' => 'purchase',
            'amount' => $amount,
            'points' => $pointsEarned
        ];
        
        return $this->createNotification($userId, 'purchase', $title, $message, $data);
    }
    
    /**
     * Notificación por cupón canjeado
     */
    public function notifyCouponRedeemed($userId, $couponName, $pointsUsed, $qrCode)
    {
        $title = "Cupón canjeado exitosamente";
        $message = "Has canjeado el cupón '{$couponName}' por {$pointsUsed} puntos. Código: {$qrCode}";
        
        $data = [
            'type' => 'coupon_redeemed',
            'coupon_name' => $couponName,
            'points_used' => $pointsUsed,
            'qr_code' => $qrCode
        ];
        
        return $this->createNotification($userId, 'coupon', $title, $message, $data);
    }
    
    /**
     * Notificación de bienvenida
     */
    public function notifyWelcome($userId, $userName)
    {
        $title = "¡Bienvenido a FidelityPoints!";
        $message = "Hola {$userName}, gracias por unirte a nuestro programa de puntos. ¡Comienza a ganar puntos con tus compras!";
        
        $data = [
            'type' => 'welcome',
            'user_name' => $userName
        ];
        
        return $this->createNotification($userId, 'welcome', $title, $message, $data);
    }
    
    /**
     * Obtener notificaciones del usuario
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
    {
        try {
            $whereClause = 'WHERE usuario_id = ?';
            $params = [$userId];
            
            if ($unreadOnly) {
                $whereClause .= ' AND leida = false';
            }
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    tipo,
                    titulo,
                    mensaje,
                    datos,
                    leida,
                    created_at
                FROM notificaciones 
                {$whereClause}
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            
            $params[] = $limit;
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markNotificationAsRead($notificationId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE notificaciones 
                SET leida = true, updated_at = NOW() 
                WHERE id = ? AND usuario_id = ?
            ');
            
            return $stmt->execute([$notificationId, $userId]);
            
        } catch (\Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsReadForUser($userId)
    {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE notificaciones 
                SET leida = true, updated_at = NOW() 
                WHERE usuario_id = ? AND leida = false
            ');
            
            return $stmt->execute([$userId]);
            
        } catch (\Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function getUnreadCount($userId)
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT COUNT(*) FROM notificaciones 
                WHERE usuario_id = ? AND leida = false
            ');
            
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
            
        } catch (\Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mostrar página de notificaciones
     */
    public function showNotifications()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            // Obtener todas las notificaciones del usuario
            $notifications = $this->getUserNotifications($_SESSION['user_id'], 50);
            
            // Contar no leídas
            $unreadCount = $this->getUnreadCount($_SESSION['user_id']);
            
            ob_start();
            include 'resources/views/notifications/index.php';
            return ob_get_clean();
            
        } catch (\Exception $e) {
            return $this->showError('Error al cargar notificaciones: ' . $e->getMessage());
        }
    }
    
    /**
     * API para obtener notificaciones (AJAX)
     */
    public function getNotificationsApi()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        
        try {
            $notifications = $this->getUserNotifications($_SESSION['user_id'], 10);
            $unreadCount = $this->getUnreadCount($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error del servidor']);
        }
    }
    
    /**
     * Procesar marcar como leída (AJAX)
     */
    public function markAsReadApi()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = $input['notification_id'] ?? null;
        
        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de notificación requerido']);
            return;
        }
        
        try {
            $success = $this->markNotificationAsRead($notificationId, $_SESSION['user_id']);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No se pudo marcar como leída']);
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error del servidor']);
        }
    }
    
    /**
     * Mostrar página de error
     */
    private function showError($message)
    {
        $error = $message;
        ob_start();
        include 'resources/views/errors/500.php';
        return ob_get_clean();
    }
}
?>