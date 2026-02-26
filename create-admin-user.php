<?php

try {
    // Configurar PDO
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec('SET search_path TO appweb, public');

    echo "=== CREANDO USUARIO ADMINISTRADOR DE PRUEBA ===\n\n";

    // Verificar si ya existe el admin
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute(['admin.test@zarzapoints.com']);
    $admin_existente = $stmt->fetch();

    if ($admin_existente) {
        echo "✅ Admin ya existe con ID: {$admin_existente['id']}\n";
    } else {
        // Crear usuario administrador
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, password, rol, telefono, fecha_nacimiento, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            'Admin',
            'ZarzaPoints',
            'Sistema',
            'admin.test@zarzapoints.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin',
            '9876543210',
            '1985-01-01'
        ]);

        $admin_id = $pdo->lastInsertId();
        echo "✅ Usuario administrador creado con ID: $admin_id\n";
    }

    echo "\n=== INFORMACIÓN DE ACCESO ADMINISTRADOR ===\n";
    echo "📧 Email: admin.test@zarzapoints.com\n";
    echo "🔑 Password: admin123\n";
    echo "👑 Rol: admin\n\n";

    // Mostrar estadísticas del sistema
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones");
    $total_cupones = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'");
    $total_clientes = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones_asignados");
    $total_asignaciones = $stmt->fetchColumn();

    echo "📊 ESTADÍSTICAS DEL SISTEMA:\n";
    echo "🎫 Total cupones: $total_cupones\n";
    echo "👥 Total clientes: $total_clientes\n";
    echo "📋 Total asignaciones: $total_asignaciones\n\n";

    echo "🚀 Ya puedes hacer login como admin en http://localhost:8000/login\n";
    echo "   y gestionar cupones en http://localhost:8000/admin/cupones\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}