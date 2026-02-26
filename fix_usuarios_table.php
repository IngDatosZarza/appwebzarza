<?php

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
    $pdo->exec('SET search_path TO appweb, public');
    
    echo "=== AGREGANDO CAMPOS FALTANTES A TABLA USUARIOS ===\n\n";
    
    // Agregar rfc
    echo "1. Agregando columna 'rfc'...\n";
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS rfc VARCHAR(13) UNIQUE");
        echo "   ✅ Columna 'rfc' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠️  " . $e->getMessage() . "\n";
    }
    
    // Agregar email_verified_at
    echo "\n2. Agregando columna 'email_verified_at'...\n";
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL");
        echo "   ✅ Columna 'email_verified_at' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠️  " . $e->getMessage() . "\n";
    }
    
    // Agregar club_zarza
    echo "\n3. Agregando columna 'club_zarza'...\n";
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS club_zarza BOOLEAN DEFAULT true");
        echo "   ✅ Columna 'club_zarza' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠️  " . $e->getMessage() . "\n";
    }
    
    // Agregar oppen_customer_id
    echo "\n4. Agregando columna 'oppen_customer_id'...\n";
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS oppen_customer_id VARCHAR(255) UNIQUE");
        echo "   ✅ Columna 'oppen_customer_id' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠️  " . $e->getMessage() . "\n";
    }
    
    // Hacer telefono unique
    echo "\n5. Agregando constraint UNIQUE a 'telefono'...\n";
    try {
        $pdo->exec("ALTER TABLE usuarios ADD CONSTRAINT usuarios_telefono_unique UNIQUE (telefono)");
        echo "   ✅ Constraint agregado\n";
    } catch (Exception $e) {
        echo "   ⚠️  " . $e->getMessage() . "\n";
    }
    
    echo "\n=== VERIFICANDO CAMBIOS ===\n\n";
    
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'usuarios' ORDER BY ordinal_position");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s | %-20s | Nullable: %-3s\n", $row['column_name'], $row['data_type'], $row['is_nullable']);
    }
    
    echo "\n✅ PROCESO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
