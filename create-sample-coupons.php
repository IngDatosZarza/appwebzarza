<?php

try {
    // Configurar PDO
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec('SET search_path TO appweb, public');

    echo "=== CREANDO CUPONES DE PRUEBA ADICIONALES ===\n\n";

    $pdo->beginTransaction();

    // Obtener ID del admin para auditoría
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute(['admin.test@zarzapoints.com']);
    $admin = $stmt->fetch();
    $admin_id = $admin ? $admin['id'] : 1;

    // Cupones de prueba
    $cupones = [
        [
            'nombre' => 'Descuento 10% Compras',
            'descripcion' => '10% de descuento en compras superiores a $500 pesos mexicanos',
            'puntos_requeridos' => 100,
            'fecha_inicio' => '2025-10-09',
            'fecha_fin' => '2025-12-31'
        ],
        [
            'nombre' => 'Envío Gratis Premium',
            'descripcion' => 'Envío gratuito en tu próxima compra sin mínimo de compra',
            'puntos_requeridos' => 150,
            'fecha_inicio' => '2025-10-09',
            'fecha_fin' => '2025-11-30'
        ],
        [
            'nombre' => 'Producto Gratis',
            'descripcion' => 'Producto gratuito de hasta $200 pesos en tu próxima visita',
            'puntos_requeridos' => 300,
            'fecha_inicio' => '2025-10-09',
            'fecha_fin' => '2025-12-15'
        ],
        [
            'nombre' => 'Descuento VIP 20%',
            'descripcion' => '20% de descuento exclusivo para clientes VIP en toda la tienda',
            'puntos_requeridos' => 500,
            'fecha_inicio' => '2025-10-15',
            'fecha_fin' => '2026-01-15'
        ],
        [
            'nombre' => 'Cupón Vencido - Ejemplo',
            'descripcion' => 'Este es un ejemplo de cupón vencido para mostrar estados',
            'puntos_requeridos' => 75,
            'fecha_inicio' => '2025-09-01',
            'fecha_fin' => '2025-10-08'
        ]
    ];

    foreach ($cupones as $cupon) {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM cupones WHERE nombre = ?");
        $stmt->execute([$cupon['nombre']]);
        $existe = $stmt->fetch();

        if (!$existe) {
            $stmt = $pdo->prepare("
                INSERT INTO cupones (nombre, descripcion, puntos_requeridos, fecha_inicio, fecha_fin, activo, actualizado_por, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, true, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $cupon['nombre'],
                $cupon['descripcion'], 
                $cupon['puntos_requeridos'],
                $cupon['fecha_inicio'],
                $cupon['fecha_fin'],
                $admin_id
            ]);

            echo "✅ Cupón creado: {$cupon['nombre']} ({$cupon['puntos_requeridos']} pts)\n";
        } else {
            echo "⏭️ Cupón ya existe: {$cupon['nombre']}\n";
        }
    }

    $pdo->commit();

    echo "\n=== ESTADÍSTICAS FINALES ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cupones");
    $total_cupones = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as activos FROM cupones WHERE activo = true");
    $cupones_activos = $stmt->fetchColumn();
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as vigentes 
        FROM cupones 
        WHERE activo = true 
        AND fecha_inicio <= CURRENT_DATE 
        AND fecha_fin >= CURRENT_DATE
    ");
    $cupones_vigentes = $stmt->fetchColumn();

    echo "🎫 Total cupones: $total_cupones\n";
    echo "✅ Cupones activos: $cupones_activos\n";
    echo "📅 Cupones vigentes: $cupones_vigentes\n\n";

    echo "🚀 Panel administrativo listo con cupones de prueba\n";
    echo "   Login admin: admin.test@zarzapoints.com / admin123\n";
    echo "   URL: http://localhost:8000/admin/cupones\n";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}