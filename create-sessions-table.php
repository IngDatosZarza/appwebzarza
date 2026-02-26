<?php
/**
 * Script para crear la tabla sessions en PostgreSQL
 * Este script resuelve el error: no existe la relación «sessions»
 */

try {
    // Configuración de la base de datos
    $host = 'localhost';
    $port = '5432';
    $dbname = 'postgres';
    $username = 'appwebuser';
    $password = 'appwebpass';
    
    // Conectar a PostgreSQL
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "🔗 Conectado a PostgreSQL exitosamente\n";
    
    // SQL para crear la tabla sessions
    $sql = "
    -- Verificar si la tabla existe
    SELECT EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = 'appweb' 
        AND table_name = 'sessions'
    );
    ";
    
    $stmt = $pdo->query($sql);
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "⚠️  La tabla 'sessions' ya existe\n";
        
        // Mostrar estructura actual
        $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_schema = 'appweb' AND table_name = 'sessions' ORDER BY ordinal_position;";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll();
        
        echo "📋 Estructura actual de la tabla sessions:\n";
        foreach ($columns as $column) {
            echo "   - {$column['column_name']}: {$column['data_type']} (" . ($column['is_nullable'] == 'YES' ? 'NULL' : 'NOT NULL') . ")\n";
        }
    } else {
        echo "📝 Creando tabla 'sessions'...\n";
        
        // SQL para crear la tabla sessions compatible con Laravel y PostgreSQL
        $createSQL = "
        CREATE TABLE appweb.sessions (
            id VARCHAR(255) NOT NULL PRIMARY KEY,
            user_id BIGINT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload TEXT NOT NULL,
            last_activity INTEGER NOT NULL
        );
        
        -- Crear índices para optimizar performance
        CREATE INDEX sessions_user_id_index ON appweb.sessions (user_id);
        CREATE INDEX sessions_last_activity_index ON appweb.sessions (last_activity);
        ";
        
        $pdo->exec($createSQL);
        echo "✅ Tabla 'sessions' creada exitosamente\n";
        
        // Insertar una sesión de prueba (opcional)
        $testSessionSQL = "
        INSERT INTO appweb.sessions (id, user_id, ip_address, user_agent, payload, last_activity) 
        VALUES 
        ('test_session_123', 1, '127.0.0.1', 'Mozilla/5.0 (Test)', 'a:3:{s:6:\"_token\";s:40:\"test123\";s:9:\"_previous\";a:1:{s:3:\"url\";s:21:\"http://localhost:8080\";}s:6:\"_flash\";a:2:{s:3:\"old\";a:0:{}s:3:\"new\";a:0:{}}}', " . time() . ")
        ON CONFLICT (id) DO NOTHING;
        ";
        
        $pdo->exec($testSessionSQL);
        echo "🧪 Sesión de prueba creada\n";
    }
    
    // Verificar la tabla
    echo "\n🔍 Verificando tabla sessions:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM appweb.sessions");
    $count = $stmt->fetchColumn();
    echo "   📊 Total de sesiones: {$count}\n";
    
    echo "\n✅ TABLA SESSIONS CONFIGURADA CORRECTAMENTE\n";
    echo "🚀 Ahora puedes usar: php artisan serve --host=localhost --port=8080\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    exit(1);
}