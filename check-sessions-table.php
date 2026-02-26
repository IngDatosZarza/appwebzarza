<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== VERIFICACIÓN DE TABLA SESSIONS ===\n\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'sessions' AND table_schema = 'appweb'");
    $existe = $stmt->fetchColumn();
    
    if ($existe > 0) {
        echo "✅ Tabla sessions existe\n";
        $stmt = $pdo->query('SELECT COUNT(*) FROM sessions');
        echo "📊 Sesiones activas: " . $stmt->fetchColumn() . "\n\n";
        
        // Mostrar estructura de la tabla
        echo "📋 Estructura de la tabla sessions:\n";
        $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'sessions' AND table_schema = 'appweb' ORDER BY ordinal_position");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['column_name'] . ": " . $row['data_type'] . "\n";
        }
    } else {
        echo "❌ Tabla sessions NO existe - esto puede causar el error 419\n";
        echo "💡 Solución: Crear la tabla sessions o cambiar driver a 'file'\n\n";
        
        echo "🔧 CREANDO TABLA SESSIONS...\n";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id varchar(255) NOT NULL,
                user_id bigint,
                ip_address varchar(45),
                user_agent text,
                payload text NOT NULL,
                last_activity integer NOT NULL,
                PRIMARY KEY (id)
            );
        ");
        
        echo "✅ Tabla sessions creada exitosamente\n";
        echo "   Ahora el sistema debería funcionar correctamente\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}