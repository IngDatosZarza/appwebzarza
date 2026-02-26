<?php

namespace App\Http\Controllers\Web;

use Exception;
use PDO;
use PDOException;
use Illuminate\Support\Facades\Session;

class TransactionController
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
        return $this->history();
    }

    public function showPurchaseForm()
    {
        return $this->purchaseForm();
    }

    public function storePurchase()
    {
        return $this->processPurchase();
    }

    public function showCoupons()
    {
        return $this->couponsForm();
    }

    public function redeemCoupon()
    {
        return $this->processCouponRedeem();
    }
    
    /**
     * Mostrar historial de transacciones del usuario
     */
    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    tp.id,
                    tp.tipo,
                    tp.puntos,
                    tp.descripcion,
                    tp.created_at,
                    ur.nombres as registrado_por_nombre,
                    ur.apellido_paterno as registrado_por_apellido
                FROM transacciones_puntos tp
                LEFT JOIN usuarios ur ON tp.registrado_por = ur.id
                WHERE tp.usuario_id = ?
                ORDER BY tp.created_at DESC
                LIMIT 50
            ');
            $stmt->execute([$_SESSION['user_id']]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener saldo actual
            $balanceStmt = $this->pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
            $balanceStmt->execute([$_SESSION['user_id']]);
            $currentBalance = $balanceStmt->fetchColumn() ?: 0;
            
            ob_start();
            include 'resources/views/transactions/history.php';
            return ob_get_clean();
            
        } catch (Exception $e) {
            return $this->showError('Error al obtener historial: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario de registro de compra
     */
    public function purchaseForm()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            // Obtener sucursales
            $stmt = $this->pdo->query('SELECT id, codigo, nombre FROM sucursales WHERE 1=1 ORDER BY nombre');
            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            ob_start();
            include 'resources/views/transactions/purchase-form.php';
            return ob_get_clean();
            
        } catch (Exception $e) {
            return $this->showError('Error al cargar formulario: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesar registro de compra
     */
    public function processPurchase()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }
        
        // Incluir el controlador de notificaciones
        require_once 'app/Http/Controllers/Web/NotificationController.php';
        $notificationController = new NotificationController();
        
        $errors = [];
        $amount = floatval($_POST['amount'] ?? 0);
        $branchId = intval($_POST['branch_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        // Validaciones
        if ($amount <= 0) {
            $errors[] = 'El monto debe ser mayor a 0';
        }
        if ($branchId <= 0) {
            $errors[] = 'Debe seleccionar una sucursal';
        }
        if (empty($description)) {
            $description = 'Compra registrada';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /purchase');
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Calcular puntos (1 punto por cada peso gastado)
            $pointsGenerated = floor($amount);
            
            // Registrar compra
            $purchaseStmt = $this->pdo->prepare('
                INSERT INTO compras (usuario_id, sucursal_id, monto, puntos_generados, creado_por, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $purchaseStmt->execute([
                $_SESSION['user_id'],
                $branchId,
                $amount,
                $pointsGenerated,
                $_SESSION['user_id']
            ]);
            
            // Registrar transacción de puntos
            $transactionStmt = $this->pdo->prepare('
                INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $transactionStmt->execute([
                $_SESSION['user_id'],
                'compra',
                $pointsGenerated,
                $description,
                $_SESSION['user_id']
            ]);
            
            // Actualizar saldo de puntos
            $updatePointsStmt = $this->pdo->prepare('
                UPDATE puntos 
                SET saldo = saldo + ?, updated_at = NOW()
                WHERE usuario_id = ?
            ');
            $updatePointsStmt->execute([$pointsGenerated, $_SESSION['user_id']]);
            
            $this->pdo->commit();
            
            // Crear notificación de compra
            $notificationController->notifyPurchase($_SESSION['user_id'], $amount, $pointsGenerated);
            
            $_SESSION['success'] = "Compra registrada exitosamente. Has ganado $pointsGenerated puntos.";
            header('Location: /transactions');
            exit;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['errors'] = ['Error al procesar compra: ' . $e->getMessage()];
            header('Location: /purchase');
            exit;
        }
    }
    
    /**
     * Mostrar cupones disponibles para canje
     */
    public function couponsForm()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            // Obtener saldo actual
            $balanceStmt = $this->pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
            $balanceStmt->execute([$_SESSION['user_id']]);
            $currentBalance = $balanceStmt->fetchColumn() ?: 0;
            
            // Obtener cupones disponibles
            $stmt = $this->pdo->query('
                SELECT id, nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin
                FROM cupones 
                WHERE activo = true 
                AND fecha_inicio <= CURRENT_DATE 
                AND fecha_fin >= CURRENT_DATE
                ORDER BY puntos_requeridos ASC
            ');
            $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener cupones ya asignados al usuario
            $assignedStmt = $this->pdo->prepare('
                SELECT cupon_id, estado, codigo_qr, created_at
                FROM cupones_asignados 
                WHERE usuario_id = ?
                ORDER BY created_at DESC
            ');
            $assignedStmt->execute([$_SESSION['user_id']]);
            $assignedCoupons = $assignedStmt->fetchAll(PDO::FETCH_ASSOC);
            
            ob_start();
            include 'resources/views/transactions/coupons.php';
            return ob_get_clean();
            
        } catch (Exception $e) {
            return $this->showError('Error al cargar cupones: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesar canje de cupón
     */
    public function processCouponRedeem()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }
        
        // Incluir el controlador de notificaciones
        require_once 'app/Http/Controllers/Web/NotificationController.php';
        $notificationController = new NotificationController();
        
        $couponId = intval($_POST['coupon_id'] ?? 0);
        
        if ($couponId <= 0) {
            $_SESSION['errors'] = ['Cupón inválido'];
            header('Location: /coupons');
            exit;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Verificar cupón y puntos requeridos
            $couponStmt = $this->pdo->prepare('
                SELECT nombre, descripcion, puntos_requeridos
                FROM cupones 
                WHERE id = ? AND activo = true 
                AND fecha_inicio <= CURRENT_DATE 
                AND fecha_fin >= CURRENT_DATE
            ');
            $couponStmt->execute([$couponId]);
            $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$coupon) {
                throw new Exception('Cupón no disponible');
            }
            
            // Verificar saldo del usuario
            $balanceStmt = $this->pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
            $balanceStmt->execute([$_SESSION['user_id']]);
            $currentBalance = $balanceStmt->fetchColumn() ?: 0;
            
            if ($currentBalance < $coupon['puntos_requeridos']) {
                throw new Exception('Puntos insuficientes para este cupón');
            }
            
            // Generar código QR único
            $qrCode = 'QR-' . uniqid() . '-' . $couponId;
            
            // Asignar cupón al usuario
            $assignStmt = $this->pdo->prepare('
                INSERT INTO cupones_asignados (usuario_id, cupon_id, estado, codigo_qr, asignado_por, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $assignStmt->execute([
                $_SESSION['user_id'],
                $couponId,
                'pendiente',
                $qrCode,
                $_SESSION['user_id']
            ]);
            
            // Registrar transacción de débito de puntos
            $transactionStmt = $this->pdo->prepare('
                INSERT INTO transacciones_puntos (usuario_id, tipo, puntos, descripcion, registrado_por, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $transactionStmt->execute([
                $_SESSION['user_id'],
                'canje',
                $coupon['puntos_requeridos'],
                'Canje de cupón: ' . $coupon['nombre'],
                $_SESSION['user_id']
            ]);
            
            // Actualizar saldo de puntos
            $updatePointsStmt = $this->pdo->prepare('
                UPDATE puntos 
                SET saldo = saldo - ?, updated_at = NOW()
                WHERE usuario_id = ?
            ');
            $updatePointsStmt->execute([$coupon['puntos_requeridos'], $_SESSION['user_id']]);
            
            $this->pdo->commit();
            
            // Crear notificación de canje
            $notificationController->notifyCouponRedeemed($_SESSION['user_id'], $coupon['nombre'], $coupon['puntos_requeridos'], $qrCode);
            
            $_SESSION['success'] = "Cupón canjeado exitosamente. Código: $qrCode";
            header('Location: /coupons');
            exit;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['errors'] = ['Error al canjear cupón: ' . $e->getMessage()];
            header('Location: /coupons');
            exit;
        }
    }
    
    /**
     * Panel de administración de puntos (solo para admins)
     */
    public function adminPanel()
    {
        // Verificar autenticación usando sesiones de Laravel
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('login')->with('error', 'Acceso denegado. Se requieren permisos de administrador.');
        }
        
        try {
            // Estadísticas generales
            $stats = [];
            
            // Total usuarios
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM usuarios WHERE rol = \'cliente\'');
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Total puntos en circulación
            $stmt = $this->pdo->query('SELECT SUM(saldo) FROM puntos');
            $stats['total_points'] = $stmt->fetchColumn() ?: 0;
            
            // Transacciones del mes
            $stmt = $this->pdo->query('
                SELECT COUNT(*) FROM transacciones_puntos 
                WHERE created_at >= DATE_TRUNC(\'month\', CURRENT_DATE)
            ');
            $stats['monthly_transactions'] = $stmt->fetchColumn();
            
            // Cupones canjeados este mes
            $stmt = $this->pdo->query('
                SELECT COUNT(*) FROM cupones_asignados 
                WHERE created_at >= DATE_TRUNC(\'month\', CURRENT_DATE)
            ');
            $stats['monthly_coupons'] = $stmt->fetchColumn();
            
            // Últimas transacciones
            $stmt = $this->pdo->query('
                SELECT 
                    tp.id,
                    tp.tipo,
                    tp.puntos,
                    tp.descripcion,
                    tp.created_at,
                    u.nombres,
                    u.apellido_paterno,
                    u.email
                FROM transacciones_puntos tp
                JOIN usuarios u ON tp.usuario_id = u.id
                ORDER BY tp.created_at DESC
                LIMIT 20
            ');
            $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top usuarios por puntos
            $stmt = $this->pdo->query('
                SELECT 
                    u.nombres,
                    u.apellido_paterno,
                    u.email,
                    p.saldo
                FROM usuarios u
                JOIN puntos p ON u.id = p.usuario_id
                WHERE u.rol = \'cliente\'
                ORDER BY p.saldo DESC
                LIMIT 10
            ');
            $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Retornar vista usando Laravel Blade
            return view('admin.points-panel', compact('stats', 'recentTransactions', 'topUsers'));
            
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Error al cargar panel de administración: ' . $e->getMessage());
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