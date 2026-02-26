<?php
/**
 * Script para añadir campos de ticket a la tabla compras
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔧 AÑADIENDO CAMPOS DE TICKET A LA TABLA COMPRAS\n";
    echo "=================================================\n\n";
    
    // Verificar si los campos ya existen
    echo "1. Verificando estructura actual...\n";
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'compras' 
        AND table_schema = 'appweb'
        AND column_name IN ('numero_ticket', 'descripcion', 'metodo_pago', 'fecha_compra')
    ");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($existingColumns) > 0) {
        echo "   ⚠️  Algunos campos ya existen: " . implode(', ', $existingColumns) . "\n";
    } else {
        echo "   ✅ Campos no existen, procediendo con la adición\n";
    }
    
    echo "\n2. Añadiendo campos necesarios...\n";
    
    // Añadir campo numero_ticket si no existe
    if (!in_array('numero_ticket', $existingColumns)) {
        $pdo->exec("ALTER TABLE compras ADD COLUMN numero_ticket VARCHAR(50)");
        echo "   ✅ Campo 'numero_ticket' añadido\n";
    } else {
        echo "   ⚠️  Campo 'numero_ticket' ya existe\n";
    }
    
    // Añadir campo descripcion si no existe
    if (!in_array('descripcion', $existingColumns)) {
        $pdo->exec("ALTER TABLE compras ADD COLUMN descripcion TEXT");
        echo "   ✅ Campo 'descripcion' añadido\n";
    } else {
        echo "   ⚠️  Campo 'descripcion' ya existe\n";
    }
    
    // Añadir campo metodo_pago si no existe
    if (!in_array('metodo_pago', $existingColumns)) {
        $pdo->exec("ALTER TABLE compras ADD COLUMN metodo_pago VARCHAR(20) DEFAULT 'efectivo'");
        $pdo->exec("ALTER TABLE compras ADD CONSTRAINT check_metodo_pago CHECK (metodo_pago IN ('efectivo', 'tarjeta', 'transferencia'))");
        echo "   ✅ Campo 'metodo_pago' añadido\n";
    } else {
        echo "   ⚠️  Campo 'metodo_pago' ya existe\n";
    }
    
    // Añadir campo fecha_compra si no existe
    if (!in_array('fecha_compra', $existingColumns)) {
        $pdo->exec("ALTER TABLE compras ADD COLUMN fecha_compra TIMESTAMP");
        echo "   ✅ Campo 'fecha_compra' añadido\n";
    } else {
        echo "   ⚠️  Campo 'fecha_compra' ya existe\n";
    }
    
    echo "\n3. Creando índices...\n";
    
    // Crear índice único para numero_ticket si no existe
    try {
        $pdo->exec("CREATE UNIQUE INDEX idx_compras_numero_ticket ON compras(numero_ticket) WHERE numero_ticket IS NOT NULL");
        echo "   ✅ Índice único 'idx_compras_numero_ticket' creado\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ⚠️  Índice 'idx_compras_numero_ticket' ya existe\n";
        } else {
            echo "   ❌ Error creando índice: " . $e->getMessage() . "\n";
        }
    }
    
    // Crear índice para fecha_compra si no existe
    try {
        $pdo->exec("CREATE INDEX idx_compras_fecha_compra ON compras(fecha_compra)");
        echo "   ✅ Índice 'idx_compras_fecha_compra' creado\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ⚠️  Índice 'idx_compras_fecha_compra' ya existe\n";
        } else {
            echo "   ❌ Error creando índice: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n4. Verificando estructura final...\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'compras' 
        AND table_schema = 'appweb'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['column_default'] ? ' DEFAULT: ' . $column['column_default'] : '';
        echo "   • {$column['column_name']}: {$column['data_type']} ({$nullable}){$default}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ CAMPOS DE TICKET AÑADIDOS EXITOSAMENTE\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "🎯 FUNCIONALIDADES HABILITADAS:\n";
    echo "   • Registro de tickets con número único\n";
    echo "   • 100 puntos fijos por cada ticket\n";
    echo "   • Descripción personalizable de compras\n";
    echo "   • Método de pago (efectivo, tarjeta, transferencia)\n";
    echo "   • Fecha de compra personalizable\n";
    echo "   • Índices para mejor rendimiento\n\n";
    
    echo "🚀 ¡SISTEMA DE TICKETS LISTO PARA USAR!\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}