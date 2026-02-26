<?php
/**
 * Helper para conexión PDO en vistas
 */

if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'pgsql:host=localhost;port=5432;dbname=postgres',
                    'appwebuser',
                    'appwebpass',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                
                // Configurar el schema
                $pdo->exec('SET search_path TO appweb, public');
                
            } catch (PDOException $e) {
                // En caso de error, crear un objeto mock que no falle
                $pdo = new class {
                    public function query($sql) {
                        return new class {
                            public function fetchColumn() { return 0; }
                            public function fetchAll() { return []; }
                            public function fetch() { return null; }
                        };
                    }
                    public function prepare($sql) {
                        return new class {
                            public function execute($params = []) { return true; }
                            public function fetchColumn() { return 0; }
                            public function fetchAll() { return []; }
                            public function fetch() { return null; }
                        };
                    }
                };
            }
        }
        
        return $pdo;
    }
}

if (!function_exists('getSystemStats')) {
    function getSystemStats() {
        $pdo = getDatabaseConnection();
        
        try {
            return [
                'usuarios' => $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() ?: 0,
                'compras' => $pdo->query('SELECT COUNT(*) FROM compras')->fetchColumn() ?: 0,
                'cupones' => $pdo->query('SELECT COUNT(*) FROM cupones WHERE activo = true')->fetchColumn() ?: 0,
                'sucursales' => $pdo->query('SELECT COUNT(*) FROM sucursales WHERE activo = true')->fetchColumn() ?: 0,
                'puntos_total' => $pdo->query('SELECT SUM(saldo) FROM puntos')->fetchColumn() ?: 0,
            ];
        } catch (Exception $e) {
            return [
                'usuarios' => 0,
                'compras' => 0,
                'cupones' => 0,
                'sucursales' => 0,
                'puntos_total' => 0,
            ];
        }
    }
}

if (!function_exists('getUserCouponsCount')) {
    function getUserCouponsCount($userId) {
        if (!$userId) return 0;
        
        $pdo = getDatabaseConnection();
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cupones_asignados WHERE usuario_id = ? AND estado = 'asignado'");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('getUserPurchasesCount')) {
    function getUserPurchasesCount($userId) {
        if (!$userId) return 0;
        
        $pdo = getDatabaseConnection();
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM compras WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('getAllUsers')) {
    function getAllUsers() {
        $pdo = getDatabaseConnection();
        try {
            $stmt = $pdo->query("
                SELECT u.nombres || ' ' || u.apellido_paterno as nombre, 
                       u.email, u.rol, 
                       COALESCE(p.saldo, 0) as puntos, 
                       u.created_at::date as fecha 
                FROM usuarios u 
                LEFT JOIN puntos p ON u.id = p.usuario_id 
                ORDER BY u.rol, u.nombres
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}