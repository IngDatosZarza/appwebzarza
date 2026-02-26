<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔍 VERIFICANDO ESTRUCTURAS DE TABLAS\n";
    echo "====================================\n\n";
    
    // Verificar tabla sucursales
    echo "📋 Tabla: sucursales\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'sucursales' 
        AND table_schema = 'appweb'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($columns)) {
        echo "   ❌ Tabla 'sucursales' no encontrada\n";
    } else {
        foreach ($columns as $column) {
            $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column['column_default'] ? ' DEFAULT: ' . $column['column_default'] : '';
            echo "   • {$column['column_name']}: {$column['data_type']} ({$nullable}){$default}\n";
        }
    }
    
    // Verificar tabla compras
    echo "\n📋 Tabla: compras\n";
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
    
    // Mostrar contenido actual de sucursales
    echo "\n📋 Contenido de sucursales:\n";
    $stmt = $pdo->query("SELECT * FROM sucursales LIMIT 5");
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sucursales)) {
        echo "   ⚠️  No hay sucursales registradas\n";
    } else {
        foreach ($sucursales as $sucursal) {
            echo "   • ID: {$sucursal['id']}, Nombre: {$sucursal['nombre']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}