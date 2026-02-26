<?php

// Script de prueba para registro de usuario

echo "=== PRUEBA DE REGISTRO DE USUARIO ===\n\n";

// Datos de prueba
$datos = [
    'nombres' => 'Juan',
    'apellido_paterno' => 'Pérez',
    'apellido_materno' => 'García',
    'email' => 'test' . time() . '@example.com',
    'email_confirmation' => '',
    'telefono' => '+525512345678',
    'fecha_nacimiento' => '1990-01-01',
    'rfc' => 'PEGJ900101ABC',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'estado' => 'JALISCO',
    'municipio' => 'GUADALAJARA',
    'codigo_postal_id' => '',
    'colonia' => 'CENTRO',
    'calle' => 'Av. Juárez',
    'numero' => '123',
];

// Configurar email de confirmación
$datos['email_confirmation'] = $datos['email'];

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    // 1. Verificar que existan códigos postales
    echo "1. Verificando códigos postales...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM codigos_postales WHERE estado = 'JALISCO' AND municipio = 'GUADALAJARA'");
    $count = $stmt->fetchColumn();
    echo "   Colonias en JALISCO/GUADALAJARA: $count\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, colonia, codigo_postal FROM codigos_postales WHERE estado = 'JALISCO' AND municipio = 'GUADALAJARA' LIMIT 1");
        $cp = $stmt->fetch(PDO::FETCH_ASSOC);
        $datos['codigo_postal_id'] = $cp['id'];
        $datos['colonia'] = $cp['colonia'];
        echo "   Usando: {$cp['colonia']} (CP: {$cp['codigo_postal']}, ID: {$cp['id']})\n";
    } else {
        echo "   ❌ No hay códigos postales disponibles\n";
        exit(1);
    }
    
    echo "\n2. Verificando estructura de tabla usuarios...\n";
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'usuarios'");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Columnas: " . implode(", ", $columnas) . "\n";
    
    // Verificar campos requeridos
    $camposRequeridos = ['nombres', 'apellido_paterno', 'apellido_materno', 'email', 'telefono', 'fecha_nacimiento', 'rfc', 'password', 'email_verified_at', 'club_zarza', 'oppen_customer_id'];
    echo "\n   Verificando campos requeridos:\n";
    foreach ($camposRequeridos as $campo) {
        $existe = in_array($campo, $columnas);
        echo "   - $campo: " . ($existe ? "✅" : "❌") . "\n";
    }
    
    echo "\n3. Verificando estructura de tabla direcciones...\n";
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'direcciones'");
    $columnasDirecciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Columnas: " . implode(", ", $columnasDirecciones) . "\n";
    
    // Verificar campos requeridos en direcciones
    $camposRequeridosDir = ['codigo_postal_id', 'municipio'];
    echo "\n   Verificando campos requeridos en direcciones:\n";
    foreach ($camposRequeridosDir as $campo) {
        $existe = in_array($campo, $columnasDirecciones);
        echo "   - $campo: " . ($existe ? "✅" : "❌") . "\n";
    }
    
    echo "\n4. Intentando crear usuario de prueba...\n";
    
    $pdo->beginTransaction();
    
    // Crear usuario
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
        $datos['nombres'],
        $datos['apellido_paterno'],
        $datos['apellido_materno'],
        $datos['email'],
        $datos['telefono'],
        $datos['fecha_nacimiento'],
        $datos['rfc'],
        password_hash($datos['password'], PASSWORD_BCRYPT)
    ]);
    
    $userId = $stmt->fetchColumn();
    echo "   ✅ Usuario creado con ID: $userId\n";
    
    // Crear dirección
    echo "\n5. Creando dirección...\n";
    $stmt = $pdo->prepare("
        INSERT INTO direcciones (
            usuario_id, calle, numero, 
            codigo_postal_id, codigo_postal, estado, municipio, colonia,
            pais, principal, created_at, updated_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'México', true, NOW(), NOW())
    ");
    
    $cpData = $pdo->query("SELECT * FROM codigos_postales WHERE id = {$datos['codigo_postal_id']}")->fetch(PDO::FETCH_ASSOC);
    
    $stmt->execute([
        $userId,
        $datos['calle'],
        $datos['numero'],
        $datos['codigo_postal_id'],
        $cpData['codigo_postal'],
        $cpData['estado'],
        $cpData['municipio'],
        $datos['colonia']
    ]);
    
    echo "   ✅ Dirección creada\n";
    
    // Crear puntos
    echo "\n6. Creando registro de puntos...\n";
    $stmt = $pdo->prepare("
        INSERT INTO puntos (usuario_id, saldo, updated_at)
        VALUES (?, 0, NOW())
    ");
    $stmt->execute([$userId]);
    echo "   ✅ Puntos creados\n";
    
    $pdo->commit();
    
    echo "\n✅ ¡REGISTRO EXITOSO!\n";
    echo "\nDatos del usuario creado:\n";
    echo "- Email: {$datos['email']}\n";
    echo "- RFC: {$datos['rfc']}\n";
    echo "- Teléfono: {$datos['telefono']}\n";
    echo "- Dirección: {$datos['calle']} {$datos['numero']}, {$datos['colonia']}\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nTraza:\n" . $e->getTraceAsString() . "\n";
}
