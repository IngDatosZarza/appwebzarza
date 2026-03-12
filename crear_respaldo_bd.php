<?php
/**
 * Script de Respaldo de Base de Datos
 * Crea un respaldo completo del esquema appweb
 */

echo "🔄 INICIANDO RESPALDO DE BASE DE DATOS\n";
echo "======================================\n\n";

// Configuración
$host = 'localhost';
$port = '5432';
$database = 'postgres';
$username = 'appwebuser';
$password = 'appwebpass';
$schema = 'appweb';

// Crear carpeta de respaldos
$backupDir = __DIR__ . '/respaldos';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✅ Carpeta de respaldos creada: $backupDir\n";
}

// Nombre del archivo con timestamp
$timestamp = date('Y-m-d_His');
$backupFile = "$backupDir/La Zarza Contigo_backup_$timestamp.sql";

echo "📁 Directorio de respaldo: $backupDir\n";
echo "📄 Archivo: " . basename($backupFile) . "\n\n";

try {
    // Conectar a la base de datos
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET search_path TO $schema, public");
    
    echo "✅ Conexión a BD establecida\n\n";
    
    // Obtener lista de tablas
    $stmt = $pdo->query("
        SELECT tablename 
        FROM pg_tables 
        WHERE schemaname = '$schema' 
        ORDER BY tablename
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Tablas encontradas: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    echo "\n";
    
    // Crear archivo de respaldo
    $sql = "-- La Zarza Contigo Database Backup\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Schema: $schema\n";
    $sql .= "-- Tables: " . count($tables) . "\n\n";
    $sql .= "SET search_path TO $schema, public;\n\n";
    
    // Para cada tabla, obtener estructura y datos
    foreach ($tables as $table) {
        echo "📦 Respaldando tabla: $table\n";
        
        // Obtener CREATE TABLE
        $sql .= "-- ============================================\n";
        $sql .= "-- Tabla: $table\n";
        $sql .= "-- ============================================\n\n";
        
        // Contar registros
        $countStmt = $pdo->query("SELECT COUNT(*) FROM $schema.$table");
        $count = $countStmt->fetchColumn();
        echo "   Registros: $count\n";
        
        // Obtener columnas
        $columnsStmt = $pdo->query("
            SELECT column_name, data_type, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_schema = '$schema' AND table_name = '$table'
            ORDER BY ordinal_position
        ");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Crear DDL simplificado
        $sql .= "DROP TABLE IF EXISTS $schema.$table CASCADE;\n";
        $sql .= "CREATE TABLE $schema.$table (\n";
        $columnDefs = [];
        foreach ($columns as $col) {
            $def = "    " . $col['column_name'] . " " . $col['data_type'];
            if ($col['column_default']) {
                $def .= " DEFAULT " . $col['column_default'];
            }
            if ($col['is_nullable'] === 'NO') {
                $def .= " NOT NULL";
            }
            $columnDefs[] = $def;
        }
        $sql .= implode(",\n", $columnDefs);
        $sql .= "\n);\n\n";
        
        // Obtener datos
        if ($count > 0) {
            $dataStmt = $pdo->query("SELECT * FROM $schema.$table");
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columnNames = array_keys($rows[0]);
                $sql .= "-- Datos de $table\n";
                
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $values[] = $value;
                        } elseif (is_bool($value)) {
                            $values[] = $value ? 'true' : 'false';
                        } else {
                            // Escapar comillas simples
                            $escaped = str_replace("'", "''", $value);
                            $values[] = "'$escaped'";
                        }
                    }
                    $sql .= "INSERT INTO $schema.$table (" . implode(", ", $columnNames) . ") VALUES (" . implode(", ", $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        echo "   ✅ Tabla respaldada\n\n";
    }
    
    // Guardar archivo
    file_put_contents($backupFile, $sql);
    
    $fileSize = filesize($backupFile);
    $fileSizeKB = round($fileSize / 1024, 2);
    
    echo "\n✅ RESPALDO COMPLETADO EXITOSAMENTE\n";
    echo "======================================\n";
    echo "📄 Archivo: $backupFile\n";
    echo "📦 Tamaño: $fileSizeKB KB\n";
    echo "📊 Tablas: " . count($tables) . "\n";
    echo "\n💡 Para restaurar en otro servidor:\n";
    echo "   psql -h HOST -U appwebuser -d postgres -f " . basename($backupFile) . "\n\n";
    
    // Crear archivo con información del respaldo
    $infoFile = "$backupDir/backup_info_$timestamp.txt";
    $info = "La Zarza Contigo - Información del Respaldo\n";
    $info .= "======================================\n";
    $info .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $info .= "Archivo: " . basename($backupFile) . "\n";
    $info .= "Tamaño: $fileSizeKB KB\n";
    $info .= "Tablas: " . count($tables) . "\n\n";
    $info .= "Lista de tablas:\n";
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM $schema.$table");
        $count = $countStmt->fetchColumn();
        $info .= "  - $table: $count registros\n";
    }
    $info .= "\nComando para restaurar:\n";
    $info .= "psql -h localhost -U appwebuser -d postgres -f " . basename($backupFile) . "\n";
    
    file_put_contents($infoFile, $info);
    echo "📋 Archivo de información creado: " . basename($infoFile) . "\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🎉 Proceso completado!\n";
