<?php
/**
 * Simple Router for Frontend Views with Authentication
 * Este archivo maneja las rutas web del frontend con sistema de autenticación
 */

session_start();

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Función para mostrar las vistas con datos de la base de datos
function showDashboard() {
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        
        ob_start();
        include 'resources/views/frontend/dashboard.php';
        return ob_get_clean();
    } catch (Exception $e) {
        return showError('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}

function showCoupons() {
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        
        ob_start();
        include 'resources/views/frontend/coupons.php';
        return ob_get_clean();
    } catch (Exception $e) {
        return showError('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}

// Incluir los controladores necesarios
require_once 'app/Http/Controllers/Web/TransactionController.php';
require_once 'app/Http/Controllers/Web/NotificationController.php';
use App\Http\Controllers\Web\TransactionController;

function showError($message) {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error - Sistema de Puntos</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-100'>
        <div class='min-h-screen flex items-center justify-center'>
            <div class='bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center'>
                <i class='fas fa-exclamation-triangle text-red-500 text-4xl mb-4'></i>
                <h1 class='text-2xl font-bold text-gray-900 mb-4'>Error</h1>
                <p class='text-gray-600 mb-6'>$message</p>
                <a href='/' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg'>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </body>
    </html>";
}

// Funciones de autenticación
function isAuthenticated() {
    return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
}

function getCurrentUser() {
    if (!isAuthenticated()) return null;
    
    return (object) [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? '',
        'nombre' => $_SESSION['user_nombre'] ?? '',
        'rol' => $_SESSION['user_rol'] ?? 'cliente',
        'puntos' => $_SESSION['user_puntos'] ?? 0,
    ];
}

function processLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return showLogin();
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email y contraseña son requeridos';
        return showLogin();
    }
    
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = true");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $_SESSION['error'] = 'Credenciales incorrectas';
            return showLogin();
        }
        
        // Actualizar último login
        $updateStmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
        $updateStmt->execute([$usuario['id']]);
        
        // Obtener puntos
        $puntosStmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
        $puntosStmt->execute([$usuario['id']]);
        $puntos = $puntosStmt->fetch(PDO::FETCH_ASSOC);
        
        // Crear sesión
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_nombre'] = $usuario['nombres'] . ' ' . $usuario['apellido_paterno'];
        $_SESSION['user_rol'] = $usuario['rol'];
        $_SESSION['user_puntos'] = $puntos['saldo'] ?? 0;
        
        $_SESSION['success'] = '¡Bienvenido de vuelta!';
        header('Location: /');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error interno del servidor';
        return showLogin();
    }
}

function processRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return showRegister();
    }
    
    $datos = [
        'nombres' => $_POST['nombres'] ?? '',
        'apellido_paterno' => $_POST['apellido_paterno'] ?? '',
        'apellido_materno' => $_POST['apellido_materno'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
        'password' => $_POST['password'] ?? '',
        'password_confirmation' => $_POST['password_confirmation'] ?? '',
    ];
    
    // Validaciones básicas
    if (empty($datos['nombres']) || empty($datos['apellido_paterno']) || empty($datos['email']) || empty($datos['password'])) {
        $_SESSION['error'] = 'Los campos marcados con * son obligatorios';
        return showRegister();
    }
    
    if ($datos['password'] !== $datos['password_confirmation']) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        return showRegister();
    }
    
    if (strlen($datos['password']) < 8) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
        return showRegister();
    }
    
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        $pdo->beginTransaction();
        
        // Verificar si el email ya existe
        $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $checkStmt->execute([$datos['email']]);
        if ($checkStmt->fetch()) {
            $_SESSION['error'] = 'El email ya está registrado';
            return showRegister();
        }
        
        // Crear usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, telefono, fecha_nacimiento, password, rol, activo, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente', true, NOW(), NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            $datos['nombres'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'],
            $datos['email'],
            $datos['telefono'],
            $datos['fecha_nacimiento'],
            password_hash($datos['password'], PASSWORD_DEFAULT)
        ]);
        
        $userId = $stmt->fetchColumn();
        
        // Crear registro de puntos inicial
        $puntosStmt = $pdo->prepare("
            INSERT INTO puntos (usuario_id, saldo, puntos_acumulados, puntos_utilizados, ultima_actualizacion, created_at, updated_at)
            VALUES (?, 0, 0, 0, NOW(), NOW(), NOW())
        ");
        $puntosStmt->execute([$userId]);
        
        $pdo->commit();
        
        // Iniciar sesión automáticamente
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $datos['email'];
        $_SESSION['user_nombre'] = $datos['nombres'] . ' ' . $datos['apellido_paterno'];
        $_SESSION['user_rol'] = 'cliente';
        $_SESSION['user_puntos'] = 0;
        
        $_SESSION['success'] = '¡Cuenta creada exitosamente! Bienvenido a FidelityPoints.';
        header('Location: /');
        exit;
        
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        $_SESSION['error'] = 'Error al crear la cuenta';
        return showRegister();
    }
}

function showLogin() {
    ob_start();
    include 'resources/views/auth/login.php';
    return ob_get_clean();
}

function showRegister() {
    ob_start();
    include 'resources/views/auth/register.php';
    return ob_get_clean();
}

function processLogout() {
    session_destroy();
    session_start();
    $_SESSION['success'] = 'Sesión cerrada exitosamente';
    header('Location: /');
    exit;
}

function processCouponRedeem() {
    if (!isAuthenticated()) {
        $_SESSION['error'] = 'Debes iniciar sesión para canjear cupones';
        header('Location: /login');
        exit;
    }
    
    $cuponId = $_POST['cupon_id'] ?? '';
    if (empty($cuponId)) {
        $_SESSION['error'] = 'Cupón no válido';
        header('Location: /cupones');
        exit;
    }
    
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
        $pdo->exec('SET search_path TO appweb, public');
        $pdo->beginTransaction();
        
        $user = getCurrentUser();
        
        // Obtener información del cupón
        $cuponStmt = $pdo->prepare("SELECT * FROM cupones WHERE id = ? AND activo = true");
        $cuponStmt->execute([$cuponId]);
        $cupon = $cuponStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cupon) {
            $_SESSION['error'] = 'Cupón no encontrado o inactivo';
            header('Location: /cupones');
            exit;
        }
        
        // Validaciones
        if ($cupon['fecha_vencimiento'] < date('Y-m-d')) {
            $_SESSION['error'] = 'El cupón ha vencido';
            header('Location: /cupones');
            exit;
        }
        
        if ($cupon['cantidad_disponible'] <= 0) {
            $_SESSION['error'] = 'No hay cupones disponibles';
            header('Location: /cupones');
            exit;
        }
        
        if ($user->puntos < $cupon['puntos_requeridos']) {
            $_SESSION['error'] = 'Puntos insuficientes para canjear este cupón';
            header('Location: /cupones');
            exit;
        }
        
        // Verificar si ya canjeó este cupón
        $checkStmt = $pdo->prepare("SELECT count(*) FROM cupones_asignados WHERE usuario_id = ? AND cupon_id = ? AND estado = 'asignado'");
        $checkStmt->execute([$user->id, $cuponId]);
        if ($checkStmt->fetchColumn() > 0 && !$cupon['multiple_uso']) {
            $_SESSION['error'] = 'Ya has canjeado este cupón anteriormente';
            header('Location: /cupones');
            exit;
        }
        
        // Descontar puntos
        $updatePuntosStmt = $pdo->prepare("
            UPDATE puntos 
            SET saldo = saldo - ?, 
                puntos_utilizados = puntos_utilizados + ?,
                ultima_actualizacion = NOW(),
                updated_at = NOW()
            WHERE usuario_id = ?
        ");
        $updatePuntosStmt->execute([$cupon['puntos_requeridos'], $cupon['puntos_requeridos'], $user->id]);
        
        // Asignar cupón al usuario
        $codigoQR = 'QR_' . strtoupper(uniqid());
        $asignarStmt = $pdo->prepare("
            INSERT INTO cupones_asignados (usuario_id, cupon_id, codigo_qr, estado, fecha_asignacion, fecha_vencimiento, created_at, updated_at)
            VALUES (?, ?, ?, 'asignado', NOW(), ?, NOW(), NOW())
        ");
        $asignarStmt->execute([$user->id, $cuponId, $codigoQR, $cupon['fecha_vencimiento']]);
        
        // Reducir cantidad disponible
        $updateCuponStmt = $pdo->prepare("UPDATE cupones SET cantidad_disponible = cantidad_disponible - 1 WHERE id = ?");
        $updateCuponStmt->execute([$cuponId]);
        
        $pdo->commit();
        
        // Actualizar puntos en sesión
        $_SESSION['user_puntos'] = $user->puntos - $cupon['puntos_requeridos'];
        
        $_SESSION['success'] = '¡Cupón canjeado exitosamente! Código: ' . $codigoQR;
        header('Location: /cupones');
        exit;
        
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        $_SESSION['error'] = 'Error al canjear cupón: ' . $e->getMessage();
        header('Location: /cupones');
        exit;
    }
}

// Router principal
switch ($path) {
    case '/':
    case '/dashboard':
        echo showDashboard();
        break;
        
    case '/cupones':
        echo showCoupons();
        break;
        
    case '/login':
        echo processLogin();
        break;
        
    case '/register':
        echo processRegister();
        break;
        
    case '/logout':
        processLogout();
        break;
        
    case '/canjear-cupon':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            processCouponRedeem();
        } else {
            header('Location: /cupones');
            exit;
        }
        break;
        
    // === NUEVAS RUTAS DEL SISTEMA DE TRANSACCIONES ===
    case '/transactions':
        $controller = new TransactionController();
        echo $controller->history();
        break;
        
    case '/purchase':
        $controller = new TransactionController();
        echo $controller->purchaseForm();
        break;
        
    case '/purchase/process':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new TransactionController();
            $controller->processPurchase();
        } else {
            header('Location: /purchase');
            exit;
        }
        break;
        
    case '/coupons':
        $controller = new TransactionController();
        echo $controller->couponsForm();
        break;
        
    case '/coupons/redeem':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new TransactionController();
            $controller->processCouponRedeem();
        } else {
            header('Location: /coupons');
            exit;
        }
        break;
        
    case '/admin/points':
        $controller = new TransactionController();
        echo $controller->adminPanel();
        break;
        
    // === RUTAS DEL SISTEMA DE NOTIFICACIONES ===
    case '/notifications':
        $controller = new \App\Http\Controllers\Web\NotificationController();
        echo $controller->showNotifications();
        break;
        
    case '/notifications/api':
        $controller = new \App\Http\Controllers\Web\NotificationController();
        $controller->getNotificationsApi();
        break;
        
    case '/notifications/mark-read':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new \App\Http\Controllers\Web\NotificationController();
            $controller->markAsReadApi();
        } else {
            header('Location: /notifications');
            exit;
        }
        break;
        
    case '/notifications/mark-all-read':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new \App\Http\Controllers\Web\NotificationController();
            $controller->markAllAsRead($_SESSION['user_id'] ?? 0);
            header('Location: /notifications');
            exit;
        } else {
            header('Location: /notifications');
            exit;
        }
        break;
        
    case '/api/health':
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'OK',
            'message' => 'API Sistema de Puntos de Fidelidad funcionando correctamente',
            'timestamp' => date('c'),
            'version' => '1.0.0'
        ]);
        break;
        
    default:
        http_response_code(404);
        echo showError('Página no encontrada');
        break;
}