<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "🔍 VERIFICANDO TABLA PUNTOS\n";
    echo "===========================\n\n";
    
    // Verificar tabla puntos
    echo "📋 Estructura de tabla puntos:\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'puntos' 
        AND table_schema = 'appweb'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['column_default'] ? ' DEFAULT: ' . $column['column_default'] : '';
        echo "   • {$column['column_name']}: {$column['data_type']} ({$nullable}){$default}\n";
    }
    
    // Mostrar contenido actual
    echo "\n📋 Contenido actual de puntos:\n";
    $stmt = $pdo->query("SELECT * FROM puntos LIMIT 5");
    $puntos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($puntos)) {
        echo "   ⚠️  No hay registros de puntos\n";
    } else {
        foreach ($puntos as $punto) {
            echo "   • Usuario ID: {$punto['usuario_id']}, Saldo: {$punto['saldo']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}