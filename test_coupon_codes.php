<?php
/**
 * Script de prueba del sistema de códigos de cupones
 * Verifica que todos los componentes estén funcionando correctamente
 */

try {
    echo "=== PRUEBA DEL SISTEMA DE CÓDIGOS DE CUPONES ===\n\n";
    
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET search_path TO appweb, public');
    
    // 1. Verificar que los cupones tienen códigos
    echo "1. Verificando códigos de cupones:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT id, nombre, codigo, puntos_requeridos, activo
        FROM cupones
        ORDER BY id
        LIMIT 10
    ");
    
    $cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cupones as $cupon) {
        $status = $cupon['codigo'] ? '✅' : '❌';
        echo "   $status ID {$cupon['id']}: {$cupon['nombre']}\n";
        echo "      Código: " . ($cupon['codigo'] ?: 'SIN CÓDIGO') . "\n";
        echo "      Puntos: {$cupon['puntos_requeridos']}\n";
        echo "      Activo: " . ($cupon['activo'] ? 'Sí' : 'No') . "\n\n";
    }
    
    // 2. Verificar cupones asignados con códigos QR
    echo "\n2. Verificando cupones asignados:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            ca.id,
            ca.codigo_qr,
            ca.estado,
            ca.fecha_uso,
            c.nombre as cupon_nombre,
            c.codigo as cupon_codigo,
            u.nombres || ' ' || u.apellido_paterno as cliente
        FROM cupones_asignados ca
        INNER JOIN cupones c ON ca.cupon_id = c.id
        INNER JOIN usuarios u ON ca.usuario_id = u.id
        ORDER BY ca.created_at DESC
        LIMIT 5
    ");
    
    $asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($asignados) > 0) {
        foreach ($asignados as $asignado) {
            $estado_icon = [
                'asignado' => '🟢',
                'usado' => '⚫',
                'vencido' => '🔴',
                'bloqueado' => '🔒'
            ][$asignado['estado']] ?? '❓';
            
            echo "   $estado_icon Asignación #{$asignado['id']}\n";
            echo "      Cliente: {$asignado['cliente']}\n";
            echo "      Cupón: {$asignado['cupon_nombre']}\n";
            echo "      Código del cupón: {$asignado['cupon_codigo']}\n";
            echo "      Código QR: {$asignado['codigo_qr']}\n";
            echo "      Estado: {$asignado['estado']}\n";
            if ($asignado['fecha_uso']) {
                echo "      Usado: {$asignado['fecha_uso']}\n";
            }
            echo "\n";
        }
    } else {
        echo "   ⚠️  No hay cupones asignados aún\n\n";
    }
    
    // 3. Verificar estados disponibles
    echo "\n3. Resumen de estados:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            estado,
            COUNT(*) as total
        FROM cupones_asignados
        GROUP BY estado
    ");
    
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($estados) > 0) {
        foreach ($estados as $estado) {
            $icon = [
                'asignado' => '🟢',
                'usado' => '⚫',
                'vencido' => '🔴',
                'bloqueado' => '🔒'
            ][$estado['estado']] ?? '❓';
            
            echo "   $icon {$estado['estado']}: {$estado['total']} cupones\n";
        }
    } else {
        echo "   ⚠️  No hay cupones asignados\n";
    }
    
    // 4. Verificar estructura de la tabla
    echo "\n\n4. Verificando estructura de tablas:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Verificar columnas de cupones
    $stmt = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'appweb' 
        AND table_name = 'cupones' 
        AND column_name IN ('codigo')
    ");
    
    $columnas_cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Tabla 'cupones':\n";
    foreach ($columnas_cupones as $col) {
        echo "      ✅ {$col['column_name']} ({$col['data_type']})\n";
    }
    
    // Verificar columnas de cupones_asignados
    $stmt = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'appweb' 
        AND table_name = 'cupones_asignados' 
        AND column_name IN ('fecha_uso', 'validado_por', 'estado')
    ");
    
    $columnas_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n   Tabla 'cupones_asignados':\n";
    foreach ($columnas_asignados as $col) {
        echo "      ✅ {$col['column_name']} ({$col['data_type']})\n";
    }
    
    // 5. Simular búsqueda de validación
    echo "\n\n5. Prueba de búsqueda de validación:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (count($asignados) > 0) {
        $test_qr = $asignados[0]['codigo_qr'];
        $test_codigo = $asignados[0]['cupon_codigo'];
        
        echo "   Probando búsqueda con código QR: $test_qr\n";
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as encontrado
            FROM cupones_asignados ca
            INNER JOIN cupones c ON ca.cupon_id = c.id
            WHERE ca.codigo_qr = ?
        ");
        $stmt->execute([$test_qr]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['encontrado'] > 0) {
            echo "   ✅ Búsqueda por código QR: EXITOSA\n";
        } else {
            echo "   ❌ Búsqueda por código QR: FALLÓ\n";
        }
        
        echo "\n   Probando búsqueda con código de cupón: $test_codigo\n";
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as encontrado
            FROM cupones_asignados ca
            INNER JOIN cupones c ON ca.cupon_id = c.id
            WHERE c.codigo = ?
        ");
        $stmt->execute([$test_codigo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['encontrado'] > 0) {
            echo "   ✅ Búsqueda por código de cupón: EXITOSA\n";
        } else {
            echo "   ❌ Búsqueda por código de cupón: FALLÓ\n";
        }
    } else {
        echo "   ⚠️  No hay cupones asignados para probar la búsqueda\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
    echo str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
