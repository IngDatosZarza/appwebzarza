<?php

function testLogin($email, $password) {
    // Configuración de conexión
    $host = 'localhost';
    $port = '5432';
    $dbname = 'postgres';
    $username = 'appwebuser';
    $password_db = 'appwebpass';
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $username, $password_db, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec("SET search_path TO appweb, public");
    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
        return false;
    }
    
    echo "Probando login para: $email\n";
    
    try {
        // Buscar usuario
        $stmt = $pdo->prepare('SELECT id, nombres, apellido_paterno, email, password, rol FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "❌ Usuario no encontrado\n\n";
            return false;
        }
        
        echo "✅ Usuario encontrado: {$user['nombres']} {$user['apellido_paterno']}\n";
        echo "   Rol: {$user['rol']}\n";
        
        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            echo "✅ Contraseña correcta\n";
            
            // Obtener puntos
            $puntosStmt = $pdo->prepare('SELECT saldo FROM puntos WHERE usuario_id = ?');
            $puntosStmt->execute([$user['id']]);
            $puntos = $puntosStmt->fetchColumn();
            
            echo "✅ Puntos del usuario: " . ($puntos ?: 0) . "\n";
            echo "✅ Login exitoso\n\n";
            return true;
        } else {
            echo "❌ Contraseña incorrecta\n\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
        return false;
    }
}

echo "=== TEST DE LOGIN ===\n\n";

// Probar usuario cliente
testLogin('cliente@test.com', 'cliente123');

// Probar usuario admin
testLogin('admin@test.com', 'admin123');

// Probar credenciales incorrectas
testLogin('cliente@test.com', 'wrong_password');
testLogin('noexiste@test.com', 'password');

echo "=== FIN DE TESTS ===\n";
?>