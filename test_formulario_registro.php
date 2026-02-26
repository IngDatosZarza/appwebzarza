<?php

echo "=== PRUEBA COMPLETA DEL FORMULARIO DE REGISTRO ===\n\n";

// Simular datos del formulario
$postData = [
    '_token' => 'test-token',
    'nombres' => 'María',
    'apellido_paterno' => 'López',
    'apellido_materno' => 'Martínez',
    'email' => 'maria' . time() . '@test.com',
    'email_confirmation' => '',
    'telefono' => '+525544332211',
    'fecha_nacimiento' => '1995-05-15',
    'rfc' => 'LOMM950515XYZ',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'estado' => 'JALISCO',
    'municipio' => 'GUADALAJARA',
    'codigo_postal_id' => '',
    'colonia' => '',
    'calle' => 'Calle Independencia',
    'numero' => '456',
];

$postData['email_confirmation'] = $postData['email'];

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // Obtener un código postal válido
    echo "1. Obteniendo código postal...\n";
    $stmt = $pdo->query("SELECT id, colonia, codigo_postal FROM codigos_postales WHERE estado = 'JALISCO' AND municipio = 'GUADALAJARA' LIMIT 1");
    $cp = $stmt->fetch(PDO::FETCH_ASSOC);
    $postData['codigo_postal_id'] = $cp['id'];
    $postData['colonia'] = $cp['colonia'];
    echo "   ✅ Usando: {$cp['colonia']} (CP: {$cp['codigo_postal']})\n";
    
    // Validar email único
    echo "\n2. Validando email único...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$postData['email']]);
    if ($stmt->fetchColumn() > 0) {
        echo "   ❌ Email ya existe\n";
        exit(1);
    }
    echo "   ✅ Email disponible\n";
    
    // Validar teléfono único
    echo "\n3. Validando teléfono único...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE telefono = ?");
    $stmt->execute([$postData['telefono']]);
    if ($stmt->fetchColumn() > 0) {
        echo "   ⚠️  Teléfono ya existe (usando uno aleatorio)\n";
        $postData['telefono'] = '+52' . rand(1000000000, 9999999999);
        echo "   Nuevo teléfono: {$postData['telefono']}\n";
    } else {
        echo "   ✅ Teléfono disponible\n";
    }
    
    // Validar RFC único
    echo "\n4. Validando RFC único...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rfc = ?");
    $stmt->execute([$postData['rfc']]);
    if ($stmt->fetchColumn() > 0) {
        echo "   ⚠️  RFC ya existe (usando uno aleatorio)\n";
        $postData['rfc'] = 'XXXX' . rand(100000, 999999) . 'XXX';
        echo "   Nuevo RFC: {$postData['rfc']}\n";
    } else {
        echo "   ✅ RFC disponible\n";
    }
    
    // Validar mayoría de edad
    echo "\n5. Validando mayoría de edad...\n";
    $fechaNac = new DateTime($postData['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNac)->y;
    if ($edad < 18) {
        echo "   ❌ Edad: $edad años (menor de edad)\n";
        exit(1);
    }
    echo "   ✅ Edad: $edad años (mayor de edad)\n";
    
    // Validar formato de teléfono
    echo "\n6. Validando formato de teléfono...\n";
    if (!preg_match('/^\+52[0-9]{10}$/', $postData['telefono'])) {
        echo "   ❌ Formato inválido: {$postData['telefono']}\n";
        exit(1);
    }
    echo "   ✅ Formato válido\n";
    
    // Validar formato de RFC
    echo "\n7. Validando formato de RFC...\n";
    if (!preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $postData['rfc']) || strlen($postData['rfc']) != 13) {
        echo "   ❌ Formato inválido: {$postData['rfc']}\n";
        exit(1);
    }
    echo "   ✅ Formato válido\n";
    
    // Proceder con el registro
    echo "\n8. Creando usuario...\n";
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (
            nombres, apellido_paterno, apellido_materno, 
            email, telefono, fecha_nacimiento, rfc,
            password, rol, club_zarza, created_at, updated_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cliente', true, NOW(), NOW())
        RETURNING id
    ");
    
    $stmt->execute([
        $postData['nombres'],
        $postData['apellido_paterno'],
        $postData['apellido_materno'],
        $postData['email'],
        $postData['telefono'],
        $postData['fecha_nacimiento'],
        strtoupper($postData['rfc']),
        password_hash($postData['password'], PASSWORD_BCRYPT)
    ]);
    
    $userId = $stmt->fetchColumn();
    echo "   ✅ Usuario creado con ID: $userId\n";
    
    // Crear dirección
    echo "\n9. Creando dirección...\n";
    $cpData = $pdo->query("SELECT * FROM codigos_postales WHERE id = {$postData['codigo_postal_id']}")->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        INSERT INTO direcciones (
            usuario_id, calle, numero, 
            codigo_postal_id, codigo_postal, estado, municipio, colonia,
            pais, principal, created_at, updated_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'México', true, NOW(), NOW())
    ");
    
    $stmt->execute([
        $userId,
        $postData['calle'],
        $postData['numero'],
        $postData['codigo_postal_id'],
        $cpData['codigo_postal'],
        $cpData['estado'],
        $cpData['municipio'],
        $postData['colonia']
    ]);
    
    echo "   ✅ Dirección creada\n";
    
    // Crear puntos
    echo "\n10. Creando registro de puntos...\n";
    $stmt = $pdo->prepare("
        INSERT INTO puntos (usuario_id, saldo, updated_at)
        VALUES (?, 0, NOW())
    ");
    $stmt->execute([$userId]);
    echo "   ✅ Puntos inicializados en 0\n";
    
    $pdo->commit();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ ¡REGISTRO COMPLETADO EXITOSAMENTE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "DATOS DEL NUEVO USUARIO:\n";
    echo "- Nombre completo: {$postData['nombres']} {$postData['apellido_paterno']} {$postData['apellido_materno']}\n";
    echo "- Email: {$postData['email']}\n";
    echo "- Teléfono: {$postData['telefono']}\n";
    echo "- RFC: {$postData['rfc']}\n";
    echo "- Fecha de nacimiento: {$postData['fecha_nacimiento']} ($edad años)\n";
    echo "- Dirección: {$postData['calle']} {$postData['numero']}, {$postData['colonia']}\n";
    echo "             {$cpData['municipio']}, {$cpData['estado']}, CP {$cpData['codigo_postal']}\n";
    echo "- Puntos iniciales: 0\n";
    echo "- Club Zarza: Sí\n";
    echo "\nPuede iniciar sesión con:\n";
    echo "  Email: {$postData['email']}\n";
    echo "  Contraseña: {$postData['password']}\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ ERROR EN REGISTRO: " . $e->getMessage() . "\n";
    echo "\nDetalles del error:\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
