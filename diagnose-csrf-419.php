<?php

// Test de creación de cupón via POST para diagnosticar el error 419

try {
    // Configurar PDO
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec('SET search_path TO appweb, public');

    echo "=== DIAGNÓSTICO DEL PROBLEMA CSRF 419 ===\n\n";

    // Simular creación de cupón directamente
    $admin_id = 37; // ID del admin creado anteriormente

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO cupones (nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        'Cupón de Prueba Directo',
        'Este cupón fue creado directamente para probar la funcionalidad',
        75,
        '2025-10-09',
        '2025-11-09',
        true,
        $admin_id
    ]);

    $cupon_id = $pdo->lastInsertId();
    $pdo->commit();

    echo "✅ Cupón creado exitosamente con ID: $cupon_id\n";
    echo "   Esto significa que el problema NO está en la base de datos\n\n";

    echo "🔍 Posibles causas del error 419:\n";
    echo "   1. Token CSRF expirado (sesión Laravel vence)\n";
    echo "   2. Formulario abierto por mucho tiempo\n";
    echo "   3. Configuración de sesiones incorrecta\n";
    echo "   4. Middleware CSRF no configurado correctamente\n\n";

    echo "💡 SOLUCIONES RECOMENDADAS:\n";
    echo "   1. Recargar la página antes de enviar el formulario\n";
    echo "   2. Verificar configuración de sesiones en config/session.php\n";
    echo "   3. Limpiar cache de Laravel\n";
    echo "   4. Usar JavaScript para renovar token automáticamente\n\n";

    echo "🚀 Para probar ahora:\n";
    echo "   1. Hacer login como admin: admin.test@zarzapoints.com / admin123\n";
    echo "   2. Ir a /admin/cupones/crear\n";
    echo "   3. Llenar el formulario inmediatamente (sin esperar)\n";
    echo "   4. Si funciona, el problema es timeout de sesión\n\n";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}