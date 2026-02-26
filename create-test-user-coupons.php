<?php

try {
    // Configurar PDO
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec('SET search_path TO appweb, public');

    echo "=== CREANDO USUARIO DE PRUEBA PARA CUPONES ===\n\n";

    $pdo->beginTransaction();

    // Verificar si ya existe el usuario
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute(['cliente.test@zarzapoints.com']);
    $usuario_existente = $stmt->fetch();

    if ($usuario_existente) {
        $usuario_id = $usuario_existente['id'];
        echo "✅ Usuario ya existe con ID: $usuario_id\n";
    } else {
        // Crear usuario de prueba
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombres, apellido_paterno, apellido_materno, email, password, rol, telefono, fecha_nacimiento, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            'Cliente',
            'De Prueba',
            'Cupones',
            'cliente.test@zarzapoints.com',
            password_hash('123456', PASSWORD_DEFAULT),
            'cliente',
            '1234567890',
            '1990-01-01'
        ]);

        $usuario_id = $pdo->lastInsertId();
        echo "✅ Usuario creado con ID: $usuario_id\n";
    }

    // Verificar si ya tiene registro de puntos
    $stmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $puntos_existentes = $stmt->fetch();

    if ($puntos_existentes) {
        // Actualizar puntos
        $stmt = $pdo->prepare("UPDATE puntos SET saldo = saldo + 500, updated_at = NOW() WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        echo "✅ Puntos actualizados (+500). Saldo anterior: {$puntos_existentes['saldo']}\n";
    } else {
        // Crear registro de puntos
        $stmt = $pdo->prepare("
            INSERT INTO puntos (usuario_id, saldo, actualizado_por, updated_at)
            VALUES (?, 500, ?, NOW())
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
        echo "✅ Registro de puntos creado con 500 puntos\n";
    }

    $pdo->commit();

    echo "\n=== INFORMACIÓN DE ACCESO ===\n";
    echo "📧 Email: cliente.test@zarzapoints.com\n";
    echo "🔑 Password: 123456\n";
    echo "💰 Puntos: 500\n";
    echo "🎫 Rol: cliente\n\n";

    // Mostrar cupones disponibles para este usuario
    $stmt = $pdo->query("
        SELECT nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin
        FROM cupones 
        WHERE activo = true 
        AND fecha_inicio <= CURRENT_DATE 
        AND fecha_fin >= CURRENT_DATE
        ORDER BY puntos_requeridos ASC
    ");
    $cupones = $stmt->fetchAll();

    echo "🎫 CUPONES DISPONIBLES PARA CANJEAR:\n";
    foreach ($cupones as $cupon) {
        $puede_canjear = 500 >= $cupon['puntos_requeridos'] ? '✅' : '❌';
        echo "  $puede_canjear {$cupon['nombre']} ({$cupon['puntos_requeridos']} puntos)\n";
        echo "    {$cupon['descripcion']}\n";
        echo "    Válido hasta: {$cupon['fecha_fin']}\n\n";
    }

    echo "🚀 Ya puedes hacer login en http://localhost:8000/login y probar los cupones\n";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}