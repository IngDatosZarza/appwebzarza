<?php
/**
 * Script para aplicar cambios a la estructura de cupones
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔧 ACTUALIZANDO ESTRUCTURA DE CUPONES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // 1. Agregar columna 'codigo' a la tabla cupones
    echo "1. Agregando columna 'codigo' a cupones...\n";
    try {
        $pdo->exec("ALTER TABLE cupones ADD COLUMN IF NOT EXISTS codigo VARCHAR(50) UNIQUE");
        echo "   ✅ Columna 'codigo' agregada\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ⚠️  Columna 'codigo' ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Actualizar estados de cupones_asignados
    echo "2. Actualizando estados de cupones_asignados...\n";
    try {
        // Crear el nuevo tipo enum
        $pdo->exec("
            DO $$ BEGIN
                CREATE TYPE cupon_estado_nuevo AS ENUM ('asignado', 'usado', 'vencido', 'bloqueado');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");
        
        // Cambiar el tipo de la columna
        $pdo->exec("
            ALTER TABLE cupones_asignados 
            ALTER COLUMN estado TYPE cupon_estado_nuevo 
            USING (
                CASE 
                    WHEN estado = 'pendiente' THEN 'asignado'::cupon_estado_nuevo
                    WHEN estado = 'redimido' THEN 'usado'::cupon_estado_nuevo
                    ELSE estado::text::cupon_estado_nuevo
                END
            )
        ");
        
        echo "   ✅ Estados actualizados: asignado, usado, vencido, bloqueado\n\n";
    } catch (Exception $e) {
        echo "   ⚠️  Error al actualizar estados: " . $e->getMessage() . "\n\n";
    }
    
    // 3. Agregar columnas de tracking de uso
    echo "3. Agregando columnas de tracking...\n";
    try {
        $pdo->exec("ALTER TABLE cupones_asignados ADD COLUMN IF NOT EXISTS fecha_uso TIMESTAMP NULL");
        echo "   ✅ Columna 'fecha_uso' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠️  fecha_uso: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE cupones_asignados ADD COLUMN IF NOT EXISTS validado_por INTEGER NULL REFERENCES usuarios(id) ON DELETE SET NULL");
        echo "   ✅ Columna 'validado_por' agregada\n\n";
    } catch (Exception $e) {
        echo "   ⚠️  validado_por: " . $e->getMessage() . "\n\n";
    }
    
    // 4. Verificar estructura final
    echo "4. Verificando estructura final...\n";
    
    // Verificar cupones
    $stmt = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'cupones' AND table_schema = 'appweb'
        ORDER BY ordinal_position
    ");
    $columnsCupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Tabla 'cupones':\n";
    foreach ($columnsCupones as $col) {
        echo "      - {$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";
    
    // Verificar cupones_asignados
    $stmt = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'cupones_asignados' AND table_schema = 'appweb'
        ORDER BY ordinal_position
    ");
    $columnsAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Tabla 'cupones_asignados':\n";
    foreach ($columnsAsignados as $col) {
        echo "      - {$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";
    
    // 5. Generar códigos para cupones existentes sin código
    echo "5. Generando códigos para cupones existentes...\n";
    $stmt = $pdo->query("SELECT id, nombre FROM cupones WHERE codigo IS NULL");
    $cuponessinCodigo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($cuponessinCodigo) > 0) {
        foreach ($cuponessinCodigo as $cupon) {
            // Generar código basado en el nombre
            $codigo = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($cupon['nombre'], 0, 15)));
            $codigo = $codigo . rand(10, 99);
            
            $updateStmt = $pdo->prepare("UPDATE cupones SET codigo = ? WHERE id = ?");
            $updateStmt->execute([$codigo, $cupon['id']]);
            
            echo "   ✅ Cupón '{$cupon['nombre']}' → Código: $codigo\n";
        }
        echo "\n";
    } else {
        echo "   ℹ️  Todos los cupones ya tienen código\n\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ ACTUALIZACIÓN COMPLETADA\n\n";
    
    echo "📋 RESUMEN:\n";
    echo "• Columna 'codigo' agregada a cupones\n";
    echo "• Estados actualizados: asignado, usado, vencido, bloqueado\n";
    echo "• Columnas de tracking agregadas: fecha_uso, validado_por\n";
    echo "• Códigos generados para cupones existentes\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
