<?php
/**
 * Script para actualizar contraseñas de usuarios de prueba
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔐 ACTUALIZANDO CONTRASEÑAS DE USUARIOS\n";
    echo "====================================\n\n";
    
    $password = 'password';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "🔧 Nueva contraseña hash: " . substr($hashedPassword, 0, 30) . "...\n\n";
    
    // Actualizar cliente
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET password = ?, updated_at = NOW() 
        WHERE email = 'cliente@test.com'
    ");
    $result1 = $stmt->execute([$hashedPassword]);
    
    // Actualizar admin
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET password = ?, updated_at = NOW() 
        WHERE email = 'admin@test.com'
    ");
    $result2 = $stmt->execute([$hashedPassword]);
    
    if ($result1 && $result2) {
        echo "✅ Contraseñas actualizadas exitosamente\n\n";
        
        // Verificar
        echo "🔍 Verificando actualización...\n";
        $stmt = $pdo->query("SELECT email, password FROM usuarios WHERE email IN ('cliente@test.com', 'admin@test.com')");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            $isValid = password_verify($password, $user['password']);
            $status = $isValid ? '✅' : '❌';
            echo "   $status {$user['email']}: " . ($isValid ? 'VÁLIDA' : 'INVÁLIDA') . "\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "🎯 AHORA PUEDES HACER LOGIN CON:\n";
        echo str_repeat("=", 50) . "\n";
        echo "📧 Email: cliente@test.com\n";
        echo "🔑 Password: password\n";
        echo "🏷️  Rol: cliente\n\n";
        echo "📧 Email: admin@test.com\n";
        echo "🔑 Password: password\n";
        echo "🏷️  Rol: admin\n\n";
        echo "🌐 Accede a: http://localhost:8000/login\n";
        
    } else {
        echo "❌ Error al actualizar contraseñas\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}